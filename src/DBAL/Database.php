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

namespace Eufony\DBAL;

use Cache\Adapter\PHPArray\ArrayCachePool;
use Eufony\DBAL\Drivers\DatabaseDriverInterface;
use Eufony\DBAL\Log\DatabaseLogger;
use Eufony\DBAL\Queries\Query;
use Eufony\DBAL\Queries\Select;
use Psr\Log\LoggerInterface;
use Psr\SimpleCache\CacheInterface;
use ReflectionObject;
use Throwable;

/**
 * Represents a connection to a database.
 * Provides methods to send queries to the database.
 *
 * Instances of this class can be statically retrieved after initialization
 * using the `Database::get()` method.
 *
 * @see \Eufony\DBAL\Database::get()
 */
class Database {

    /**
     * Stores active instances of database connections.
     *
     * @var array $connections
     */
    private static array $connections = [];

    /**
     * A backend driver for generating and executing queries.
     *
     * @var \Eufony\DBAL\Drivers\DatabaseDriverInterface $driver
     */
    private DatabaseDriverInterface $driver;

    /**
     * A PSR-3 compliant logger.
     * Defaults to an instance of `\Eufony\DBAL\Log\DatabaseLogger`.
     *
     * @var \Psr\Log\LoggerInterface $logger
     */
    private LoggerInterface $logger;

    /**
     * A PSR-16 compliant cache.
     * Defaults to an instance of an array cache pool implementation.
     *
     * @var \Psr\SimpleCache\CacheInterface
     */
    private CacheInterface $cache;

    /**
     * Returns a previously initialized instance of a database connection.
     * If no key is specified, the "default" database is returned.
     *
     * @param string $key
     * @return \Eufony\DBAL\Database
     */
    public static function get(string $key = "default"): Database {
        if (!array_key_exists($key, static::$connections)) {
            throw new InvalidArgumentException("Unknown database connection '$key'");
        }

        return static::$connections[$key];
    }

    /**
     * Returns all active instances of database connections.
     *
     * @return array<\Eufony\DBAL\Database>
     */
    public static function connections(): array {
        return static::$connections;
    }

    /**
     * Class constructor.
     * Creates a new connection to a database.
     *
     * Requires a database driver backend and a key to refer to the connection.
     * The key can later be used to fetch this instance using the
     * `Database::get()` method.
     *
     * By default, sets up a `\Eufony\DBAL\Log\DatabaseLogger` for logging and
     * an array cache pool for caching.
     *
     * @param \Eufony\DBAL\Drivers\DatabaseDriverInterface $driver
     * @param string $key
     *
     * @see \Eufony\DBAL\Database::get()
     */
    public function __construct(DatabaseDriverInterface $driver, string $key = "default") {
        static::$connections[$key] = $this;
        $this->driver = $driver;
        $this->logger = new DatabaseLogger($this);
        $this->cache = new ArrayCachePool();
    }

    /**
     * Returns the current database driver.
     *
     * @return \Eufony\DBAL\Drivers\DatabaseDriverInterface
     */
    public function driver(): DatabaseDriverInterface {
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
     * @param \Psr\SimpleCache\CacheInterface|null $cache
     * @return \Psr\SimpleCache\CacheInterface
     */
    public function cache(?CacheInterface $cache = null): CacheInterface {
        $prev = $this->cache;
        $this->cache = $cache ?? $this->cache;
        return $prev;
    }

    /**
     * Executes the given query.
     *
     * Additionally handles caching (for read-only queries), logging, and
     * generation of the query string from a query builder.
     *
     * The query, whether passed in as a string directly or built using a query
     * builder, is passed to the `DatabaseDriverInterface::execute()` method
     * along with the context array, providing easy protection against SQL
     * injection attacks.
     *
     * Throws a `\Eufony\DBAL\QueryException` on failure.
     *
     * @param string|\Eufony\DBAL\Queries\Query $query
     * @param array<mixed> $context
     * @return array<array<mixed>>
     * @throws \Eufony\DBAL\QueryException
     *
     * @see \Eufony\DBAL\Drivers\DatabaseDriverInterface::execute()
     */
    public function query(string|Query $query, array $context = []): array {
        // If a query builder is passed, determine if it mutates data in the database
        // Otherwise, cannot determine if the string is a mutation, set to null
        /** @var bool|null $is_mutation */
        $is_mutation = $query instanceof Query ? get_class($query) === Select::class : null;

        // If the query was built using a query builder, generate the query string
        $query_string = $query instanceof Query ? $this->driver->generate($query) : $query;

        // For read-only queries, check if the result is cached first
        if ($is_mutation === false) {
            // Hashing the query ensures the cache key matches PSR-16 standards
            // on the valid character set and maximum supported length
            $cache_key = hash("sha256", $query_string);

            if ($this->cache->has($cache_key)) {
                $this->logger->debug("Query cache hit: $query_string");
                return $this->cache->get($cache_key);
            }
        }

        // Execute query
        try {
            $query_result = $this->driver->execute($query_string, $context);
        } catch (InvalidArgumentException | QueryException $e) {
            $message = $e instanceof InvalidArgumentException
                ? "Mismatched placeholders and parameters in the query and context array."
                : "Query failed: $query_string";

            // Overwrite exception message with default message if it is empty
            if (strlen($e->getMessage()) === 0) {
                $prop = (new ReflectionObject($e))->getProperty("message");
                $prop->setAccessible(true);
                $prop->setValue($e, $message);
                $prop->setAccessible(false);
            }

            // Log error for query exceptions
            if ($e instanceof QueryException) {
                $this->logger->error($message, context: ["exception" => $e]);
            }

            throw $e;
        }

        if ($is_mutation === true) {
            // Log notice for write operations
            $this->logger->notice("Query write op: $query_string");

            // Invalidate cache
            // TODO: Don't need to invalidate the entire cache, only the tables that were altered
            $this->cache->clear();
            $this->logger->debug("Clear cache");
        } elseif ($is_mutation === false) {
            // Log info for read operations
            $this->logger->info("Query read op: $query_string");

            // Cache result
            $this->cache->set($cache_key, $query_result, ttl: 3600);
            $this->logger->debug("Query cached result: $query_string");
        } else {
            // Log notice of unknown operations
            $this->logger->notice("Query (unknown type): $query_string");

            // Invalidate the entire cache, just to be safe
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
     * If an exception occurs, the changes are rolled back and the exception is
     * re-thrown.
     *
     * This method can be nested within itself.
     * This does not actually nest real transactions, the nested call to start
     * a transaction is simply be ignored without resulting in error.
     *
     * @param callable $callback
     * @throws Throwable
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
            throw $e;
        }

        if ($is_root_transaction) {
            // Transaction didn't throw errors, commit to database
            $this->driver->commit();
            $this->logger->debug("Commit transaction");
        }
    }

}
