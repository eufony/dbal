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

namespace Eufony\ORM;

use Eufony\ORM\DBAL\Adapters\SqlAdapterInterface;
use Eufony\ORM\Loggers\SqlLogger;
use Psr\Log\LoggerInterface;
use Psr\SimpleCache\CacheInterface;

/**
 * Represents a connection to a database.
 * Provides a method to send queries to the database.
 *
 * Instances of this class can be statically retrieved after initialization
 * using the `Database::get()` method.
 *
 * @see \Eufony\ORM\Database::get()
 */
class Database {

    /**
     * Stores active instances of database connections.
     *
     * @var array $connections
     */
    private static array $connections = [];

    /**
     * A backend driver for translating and executing SQL queries.
     *
     * @var \Eufony\ORM\DBAL\Adapters\SqlAdapterInterface $adapter
     */
    private SqlAdapterInterface $adapter;

    /**
     * A PSR-3 compliant logger.
     * Defaults to an instance of `\Eufony\ORM\Loggers\SqlLogger`.
     *
     * @var \Psr\Log\LoggerInterface $logger
     */
    private LoggerInterface $logger;

    /**
     * A PSR-16 compliant cache.
     * Defaults to an array cache pool implementation.
     *
     * @var \Psr\SimpleCache\CacheInterface
     */
    private CacheInterface $cache;

    /**
     * Returns a previously initialized instance of a database connection.
     * If no key is specified, the default database will be returned.
     *
     * @param string $key
     * @return \Eufony\ORM\Database
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
     * @return array<\Eufony\ORM\Database>
     */
    public static function connections(): array {
        return static::$connections;
    }

    /**
     * Class constructor.
     * Creates a connection to a database.
     *
     * Requires a key to refer to the database and an SQL adapter backend.
     * The key can later be used to fetch this instance using the
     * `Database::get()` method.
     *
     * By default, sets up a `\Eufony\ORM\Loggers\SqlLogger` for logging and an
     * array cache pool for caching.
     *
     * Notice: A database with the key `default` MUST be set up. This will be
     * used internally by the ORM for schema validation, logging, etc.
     *
     * @param string $key
     * @param \Eufony\ORM\DBAL\Adapters\SqlAdapterInterface $adapter
     *
     * @see \Eufony\ORM\Database::get()
     */
    public function __construct(string $key, SqlAdapterInterface $adapter) {
        static::$connections[$key] = $this;
        $this->adapter = $adapter;
        $this->logger = new SqlLogger();
        $this->cache = new \Cache\Adapter\PHPArray\ArrayCachePool();
    }

    /**
     * Class destructor.
     * Breaks the connection to the database.
     */
    public function __destruct() {
        $this->adapter->disconnect();
    }

    /**
     * Returns the current SQL adapter.
     *
     * @return \Eufony\ORM\DBAL\Adapters\SqlAdapterInterface
     */
    public function adapter(): SqlAdapterInterface {
        return $this->adapter;
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
     * Executes the given SQL query.
     *
     * Additionally handles caching (for read-only queries), logging, and
     * translation of the query into different database backends.
     * The caching can be turned on or off using the `$cache` parameter.
     *
     * The query, along with the context array, is first passed to the
     * `SqlParser::prepare()` method, providing easy protection against SQL
     * injection attacks using prepared statements.
     *
     * Throws a `\Eufony\ORM\QueryException` on failure.
     *
     * @param string $sql
     * @param array<mixed> $context
     * @param bool $cache
     * @return array<array<mixed>>
     * @throws \Eufony\ORM\QueryException
     *
     * @see \Eufony\ORM\SqlParser::prepare()
     */
    public function query(string $sql, array $context = [], bool $cache = true): array {
        // Parse placeholders in query
        $sql = SqlParser::prepare($sql, $context);

        // Determine if query mutates data in the database with the first keyword
        $mutation_keywords = ["INSERT", "UPDATE", "DELETE", "CREATE", "DROP", "ALTER"];
        $is_mutation = preg_match("/^" . implode("|", $mutation_keywords) . "/", $sql) === 1;

        // For read-only queries, check if the result is cached first
        if (!$is_mutation && $cache) {
            // Hashing the query ensures the cache key matches PSR-16 standards
            // on the valid character set and maximum supported length
            $cache_key = hash("sha256", $sql);

            if ($this->cache->has($cache_key)) {
                $this->logger->debug("Query cache hit: $sql");
                return $this->cache->get($cache_key);
            }
        }

        // Translate SQL into the query language of the backend
        // Log translated query if backend changed it
        $query = $this->adapter->translate($sql);
        if ($query !== $sql) $this->logger->debug("Query SQL translated to: $query");

        // Execute query
        try {
            $query_result = $this->adapter->execute($query, $sql);
        } catch (QueryException $e) {
            $message = "Query failed: $sql";

            // Overwrite exception message with query string if message is empty
            if (strlen($e->getMessage()) === 0) {
                $prop = (new \ReflectionObject($e))->getProperty("message");
                $prop->setAccessible(true);
                $prop->setValue($e, $message);
                $prop->setAccessible(false);
            }

            $this->logger->error($message, context: ["exception" => $e]);
            throw $e;
        }

        if ($is_mutation) {
            // Log notice for write operations
            $this->logger->notice("Query write op: $sql");

            // Invalidate cache
            // TODO: Don't need to invalidate the entire cache, only the tables that were altered
            $this->cache->clear();
        } else {
            // Log info for read operations
            $this->logger->info("Query read op: $sql");

            // Cache result
            if ($cache) {
                $this->cache->set($cache_key, $query_result, ttl: 3600);
                $this->logger->debug("Query cached result: $sql");
            }
        }

        // Return result
        return $query_result;
    }

}
