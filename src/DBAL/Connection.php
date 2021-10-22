<?php
/*
 * The Eufony ORM Package
 * Copyright (c) 2021 Alpin Gencer
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published
 * by the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful, but
 * WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License
 * along with this program. If not, see <https://www.gnu.org/licenses/>.
 */

namespace Eufony\ORM\DBAL;

use DateInterval;
use Eufony\ORM\DBAL\Driver\DriverInterface;
use Eufony\ORM\Cache\ArrayCache;
use Eufony\ORM\InvalidArgumentException;
use Eufony\ORM\Log\DatabaseLogger;
use Eufony\ORM\QueryException;
use Eufony\ORM\TransactionException;
use Psr\Log\LoggerInterface;
use Psr\SimpleCache\CacheInterface;
use Throwable;

/**
 * Represents a connection to a database.
 * Provides methods to send queries to the database engine.
 */
class Connection {

    /**
     * A backend driver for building and executing queries.
     *
     * @var \Eufony\ORM\DBAL\Driver\DriverInterface $driver
     */
    private DriverInterface $driver;

    /**
     * A PSR-3 compliant logger.
     * Defaults to an instance of `\Eufony\ORM\Log\DatabaseLogger`.
     *
     * @var \Psr\Log\LoggerInterface $logger
     */
    private LoggerInterface $logger;

    /**
     * A PSR-16 compliant cache.
     * Defaults to an instance of `\Eufony\ORM\Cache\ArrayCache`.
     *
     * @var \Psr\SimpleCache\CacheInterface
     */
    private CacheInterface $cache;

    /**
     * Class constructor.
     * Creates a new connection to a database engine using the given driver
     * backend.
     *
     * By default, sets up a `\Eufony\ORM\Log\DatabaseLogger` for logging,
     * and a `\Eufony\ORM\Cache\ArrayCache` for caching.
     *
     * @param \Eufony\ORM\DBAL\Driver\DriverInterface $driver
     */
    public function __construct(DriverInterface $driver) {
        $this->driver = $driver;
        $this->logger = new DatabaseLogger($this);
        $this->cache = new ArrayCache();
    }

    /**
     * Returns the current database driver.
     *
     * @return \Eufony\ORM\DBAL\Driver\DriverInterface
     */
    public function driver(): DriverInterface {
        return $this->driver;
    }

    /**
     * Returns the current PSR-3 logger.
     * If `$logger` is set, sets the new logger and returns the previous
     * instance.
     *
     * @param \Psr\Log\LoggerInterface|null $logger
     * @return \Psr\Log\LoggerInterface
     */
    public function logger(?LoggerInterface $logger = null): LoggerInterface {
        $prev = $this->logger;
        $this->logger = $logger ?? $this->logger;
        return $prev;
    }

    /**
     * Returns the current PSR-16 cache.
     * If `$cache` is set, sets the new cache and returns the previous
     * instance.
     *
     * **Tip**: If your caching implementation only supports the PSR-6
     * standards, and not the PSR-16 standards, you can wrap it in a
     * `\Eufony\ORM\Cache\Psr16Adapter` for easy interoperability.
     *
     * @param \Psr\SimpleCache\CacheInterface|null $cache
     * @return \Psr\SimpleCache\CacheInterface
     */
    public function cache(?CacheInterface $cache = null): CacheInterface {
        $prev = $this->cache;
        $this->cache = $cache ?? $this->cache;
        return $prev;
    }

    /**
     * Executes the given query string.
     *
     * Additionally handles caching (for read-only queries), logging, and
     * generation of the query string from a query builder.
     *
     * The query is passed to the `DriverInterface::execute()` method along
     * with the context array, providing easy protection against SQL injection
     * attacks.
     *
     * The cached result's expiration can be set using the `$ttl` parameter;
     * either as a `DateInterval` object or an integer number of minutes.
     * Defaults to 1 minute.
     * If `$ttl` is set to null, the query result will not be cached.
     *
     * Throws a `\Eufony\ORM\QueryException` on failure.
     *
     * @param string $query
     * @param mixed[] $context
     * @param int|\DateInterval|null $ttl
     * @return mixed[][]
     */
    public function query(string $query, array $context = [], int|DateInterval|null $ttl = 1): array {
        // Determine if query mutates data in the database depending on the first keyword
        $is_mutation = preg_match("/^SELECT/", $query) !== 1;

        // For read-only queries, check if the result is cached first
        if ($is_mutation === false && $ttl !== null) {
            // Hashing the query along with the context array ensures the cache
            // key matches PSR-16 standards on the valid character set and
            // maximum supported length
            // Sorting the context array ensures predictability when hashing
            asort($context);
            $cache_key = hash("sha256", $query . implode("|", $context));
            $cache_result = $this->cache->get($cache_key);

            if ($cache_result !== null) {
                $this->logger->debug("Query cache hit: $query");
                return $cache_result;
            }
        }

        // Execute query
        try {
            $query_result = $this->driver->execute($query, $context);
        } catch (InvalidArgumentException | QueryException $e) {
            // Log error for query exceptions
            if ($e instanceof QueryException) {
                $this->logger->error("Query failed: $query", context: ["exception" => $e]);
            }

            throw $e;
        }

        if ($is_mutation === false) {
            if ($ttl !== null) {
                // Log info for read operations
                $this->logger->info("Query read op: $query");

                // Cache result
                $this->cache->set($cache_key, $query_result, ttl: $ttl);
                $this->logger->debug("Query cached result: $query");
            }
        } else {
            // Log notice for write operations
            $this->logger->notice("Query write op: $query");

            // Invalidate cache
            // TODO: Don't need to invalidate the entire cache, only the tables that were altered
            $this->cache->clear();
            $this->logger->debug("Clear cache");
        }

        // Return result
        return $query_result;
    }

    /**
     * Wraps the given callback function in a transaction.
     * Changes to the database are only committed if the callback does not
     * result in an exception.
     *
     * If an exception occurs, the changes are rolled back and a
     * `\Eufony\ORM\TransactionException` is thrown.
     *
     * This method can be nested within itself.
     * This does not actually nest real transactions, the nested call to start
     * a transaction is simply be ignored without resulting in error.
     *
     * @param callable $callback
     */
    public function transactional(callable $callback): void {
        // Check for nested transactions
        // Only the root transaction issues calls to begin transactions, commits, and rollbacks
        $is_root_transaction = !$this->driver->inTransaction();

        if ($is_root_transaction) {
            // Start transaction
            $this->logger->debug("Start transaction");
            $this->driver->beginTransaction();
        }

        try {
            // Call callback function
            call_user_func_array($callback, [$this]);
        } catch (Throwable $e) {
            if ($is_root_transaction) {
                // Transaction failed, roll back
                $this->driver->rollback();
                $this->logger->error("Transaction failed, roll back", context: ["exception" => $e]);
            }

            // Propagate error
            throw new TransactionException("Transaction failed", previous: $e);
        }

        if ($is_root_transaction) {
            // Transaction didn't throw errors, commit to database
            $this->driver->commit();
            $this->logger->debug("Commit transaction");
        }
    }

}
