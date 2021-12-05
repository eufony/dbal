<?php
/*
 * The Eufony ORM
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
use Eufony\Cache\ArrayCache;
use Eufony\Inflector\DoctrineInflector;
use Eufony\Inflector\InflectorInterface;
use Eufony\ORM\BadMethodCallException;
use Eufony\ORM\DBAL\Driver\DriverInterface;
use Eufony\ORM\InvalidArgumentException;
use Eufony\ORM\Log\DatabaseLogger;
use Eufony\ORM\QueryException;
use Eufony\ORM\TransactionException;
use Psr\Log\LoggerInterface;
use Psr\SimpleCache\CacheInterface;
use Throwable;

/**
 * Represents a connection to a database.
 *
 * Provides methods to send queries to the database engine.
 */
class Connection
{
    /**
     * Stores the active instances of the database connections.
     *
     * @var \Eufony\ORM\DBAL\Connection[] $connections
     */
    protected static array $connections;

    /**
     * The key used to refer to this database connection.
     *
     * @var string $key
     */
    protected string $key;

    /**
     * A backend driver for building and executing queries.
     *
     * @var \Eufony\ORM\DBAL\Driver\DriverInterface $driver
     */
    protected DriverInterface $driver;

    /**
     * A PSR-16 compliant cache.
     *
     * Defaults to an instance of `\Eufony\Cache\ArrayCache`.
     *
     * @var \Psr\SimpleCache\CacheInterface
     */
    protected CacheInterface $cache;

    /**
     * An Inflector implementation.
     *
     * Defaults to an instance of `\Eufony\Inflector\DoctrineInflector`.
     *
     * @var \Eufony\Inflector\InflectorInterface $inflector
     */
    protected InflectorInterface $inflector;

    /**
     * A PSR-3 compliant logger.
     *
     * Defaults to an instance of `\Eufony\ORM\Log\DatabaseLogger`.
     *
     * @var \Psr\Log\LoggerInterface $logger
     */
    protected LoggerInterface $logger;

    /**
     * Returns an active instance of the database connection with the given key, or
     * the default key if none is given.
     *
     * @param string|null $key
     * @return \Eufony\ORM\DBAL\Connection
     */
    public static function get(?string $key = null): Connection
    {
        $key ??= "default";

        // Ensure connection exists
        if (!array_key_exists($key, static::$connections)) {
            throw new BadMethodCallException("No active database connection with key '$key'");
        }

        return static::$connections[$key];
    }

    /**
     * Class constructor.
     * Creates a new connection to a database engine using the given driver backend
     * and (optionally) key.
     *
     * The database key defaults to the value `default`.
     *
     * Sets up an array cache for caching, and a `\Eufony\ORM\Log\DatabaseLogger`
     * for logging.
     *
     * @param \Eufony\ORM\DBAL\Driver\DriverInterface $driver
     * @param string|null $key
     */
    public function __construct(DriverInterface $driver, ?string $key = null)
    {
        $key ??= "default";
        $this->key = $key;
        static::$connections[$this->key] = $this;

        $this->driver = $driver;

        $this->cache = new ArrayCache();
        $this->inflector = new DoctrineInflector();
        $this->logger = new DatabaseLogger($this);
    }

    /**
     * Class destructor.
     * Removes this database connection from the list of active connections.
     */
    public function __destruct()
    {
        // Unset reference to this active connection
        if (static::get($this->key) === $this) {
            unset(static::$connections[$this->key]);
        }
    }

    /**
     * Getter for the database key.
     *
     * Returns the key used to refer to this connection.
     *
     * @return string
     */
    public function key(): string
    {
        return $this->key;
    }

    /**
     * Getter for the database driver.
     *
     * Returns the current database driver.
     *
     * @return \Eufony\ORM\DBAL\Driver\DriverInterface
     */
    public function driver(): DriverInterface
    {
        return $this->driver;
    }

    /**
     * Combined getter / setter for the PSR-16 cache.
     *
     * Returns the current PSR-16 cache.
     * If `$cache` is set, sets the new cache and returns the previous instance.
     *
     * @param \Psr\SimpleCache\CacheInterface|null $cache
     * @return \Psr\SimpleCache\CacheInterface
     */
    public function cache(?CacheInterface $cache = null): CacheInterface
    {
        $prev = $this->cache;
        $this->cache = $cache ?? $this->cache;
        return $prev;
    }

    /**
     * Combined getter / setter for the Inflector implementation.
     *
     * Returns the current inflector.
     * If `$inflector` is set, sets the new inflector and returns the previous
     * instance.
     *
     * @param \Eufony\Inflector\InflectorInterface|null $inflector
     * @return \Eufony\Inflector\InflectorInterface
     */
    public function inflector(?InflectorInterface $inflector = null): InflectorInterface
    {
        $prev = $this->inflector;
        $this->inflector = $inflector ?? $this->inflector;
        return $prev;
    }

    /**
     * Combined getter / setter for the PSR-3 logger.
     *
     * Returns the current PSR-3 logger.
     * If `$logger` is set, sets the new logger and returns the previous instance.
     *
     * @param \Psr\Log\LoggerInterface|null $logger
     * @return \Psr\Log\LoggerInterface
     */
    public function logger(?LoggerInterface $logger = null): LoggerInterface
    {
        $prev = $this->logger;
        $this->logger = $logger ?? $this->logger;
        return $prev;
    }

    /**
     * Executes the given query string.
     *
     * Additionally handles caching (for read-only queries) and logging.
     *
     * The query is passed to the `DriverInterface::execute()` method along with
     * the context array, providing easy protection against SQL injection attacks.
     *
     * The cached result's expiration time can be set using the `$ttl` parameter;
     * either as a `DateInterval` object or as an integer number of minutes.
     * The default expiration is 1 minute.
     * If `$ttl` is set to null, the query result will not be cached.
     *
     * Throws a `\Eufony\ORM\QueryException` on failure.
     *
     * @param string $query
     * @param mixed[] $context
     * @param int|\DateInterval|null $ttl
     * @return mixed[][]
     */
    public function query(string $query, array $context = [], int|DateInterval|null $ttl = 1): array
    {
        // Determine if query mutates data in the database depending on the first keyword
        $is_mutation = preg_match("/^SELECT/", $query) !== 1;

        // For read-only queries, check if the result is cached first
        if ($is_mutation === false && $ttl !== null) {
            // Hashing the query along with the context array ensures the cache key matches
            // the PSR-16 standards on the valid character set and maximum supported length
            // Sorting the context array ensures predictability when hashing
            asort($context);
            $cache_key = hash("sha256", $query . implode("|", $context));
            $cache_result = $this->cache->get($cache_key);

            if ($cache_result !== null) {
                $this->logger->debug("Cache hit for query: $query");
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
                $this->logger->info("Query read: $query");

                // Cache result
                $this->cache->set($cache_key, $query_result, ttl: $ttl);
                $this->logger->debug("Cache set for query: $query");
            }
        } else {
            // Log notice for write operations
            $this->logger->notice("Query mutation: $query");

            // Invalidate cache
            // TODO: Don't need to invalidate the entire cache, only the tables that were altered
            $this->cache->clear();
            $this->logger->debug("Cache clear");
        }

        // Return result
        return $query_result;
    }

    /**
     * Wraps the given callback function in a transaction.
     *
     * Changes to the database are only committed if the callback does not result
     * in an exception.
     *
     * If an exception occurs, the changes are rolled back and a
     * `\Eufony\ORM\TransactionException` is thrown.
     *
     * This method can be nested within itself.
     * This does not actually nest "real" transactions, the nested call to start a
     * transaction is simply be ignored without resulting in an error.
     *
     * @param callable $callback
     */
    public function transactional(callable $callback): void
    {
        // Check for nested transactions
        // Only the root transaction issues calls to begin, commit, and roll back transactions
        $is_root_transaction = !$this->driver->inTransaction();

        // Start transaction
        if ($is_root_transaction) {
            $this->logger->debug("Transaction start");
            $this->driver->beginTransaction();
        }

        try {
            // Call callback function
            call_user_func_array($callback, [$this]);
        } catch (Throwable $e) {
            // Transaction failed, roll back
            if ($is_root_transaction) {
                $this->driver->rollback();
                $this->logger->error("Transaction failed, rolled back", context: ["exception" => $e]);
            }

            // Propagate error
            throw new TransactionException("Transaction failed, rolled back", previous: $e);
        }

        // Commit transaction
        if ($is_root_transaction) {
            $this->driver->commit();
            $this->logger->debug("Transaction commit");
        }
    }
}
