<?php
/*
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

use BadMethodCallException;
use DateInterval;
use Eufony\Cache\Adapter\TagAwarePsr16Adapter;
use Eufony\Cache\Pool\NullCache;
use Eufony\Cache\TagAwareInterface;
use Eufony\Cache\Utils\CacheKeyProvider;
use Eufony\DBAL\Driver\DriverInterface;
use Eufony\DBAL\Query\Builder\Query;
use Eufony\DBAL\Query\Builder\Select;
use InvalidArgumentException;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
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
     * @var \Eufony\DBAL\Connection[] $connections
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
     * @var \Eufony\DBAL\Driver\DriverInterface $driver
     */
    protected DriverInterface $driver;

    /**
     * A PSR-16 compliant cache.
     *
     * Defaults to an instance of a null cache.
     *
     * @var \Psr\SimpleCache\CacheInterface&\Eufony\Cache\TagAwareInterface
     */
    protected CacheInterface&TagAwareInterface $cache;

    /**
     * A PSR-3 compliant logger.
     *
     * Defaults to an instance of a null logger.
     *
     * @var \Psr\Log\LoggerInterface $logger
     */
    protected LoggerInterface $logger;

    /**
     * Returns an active instance of the database connection with the given key, or
     * the default key if none is given.
     *
     * @param string|null $key
     * @return \Eufony\DBAL\Connection
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
     * @param \Eufony\DBAL\Driver\DriverInterface $driver
     * @param string|null $key
     */
    public function __construct(DriverInterface $driver, ?string $key = null)
    {
        $this->key = $key ?? "default";
        static::$connections[$this->key] = $this;

        $this->driver = $driver;
        $this->cache = new TagAwarePsr16Adapter(new NullCache());
        $this->logger = new NullLogger();
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
     * @return \Eufony\DBAL\Driver\DriverInterface
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
     * @param \Psr\SimpleCache\CacheInterface&\Eufony\Cache\TagAwareInterface|null $cache
     * @return \Psr\SimpleCache\CacheInterface&\Eufony\Cache\TagAwareInterface
     */
    public function cache(CacheInterface $cache = null): CacheInterface&TagAwareInterface
    {
        if ($cache !== null && !($cache instanceof TagAwareInterface)) {
            throw new InvalidArgumentException("Cache implementation must support TagAwareInterface.");
        }

        $prev = $this->cache;
        $this->cache = $cache ?? $this->cache;
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
     * Executes the given query.
     *
     * Additionally handles caching (for read-only queries) and logging.
     *
     * The query is passed to the `DriverInterface::execute()` method along with
     * its context array, providing easy protection against SQL injection attacks.
     *
     * The cached result's expiration time can be set using the `$ttl` parameter;
     * either as a `DateInterval` object or as an integer number of seconds.
     * The default expiration is 1 minute.
     * If `$ttl` is set to null, the query result will not be cached.
     *
     * Throws a `\Eufony\DBAL\QueryException` on failure.
     *
     * @param \Eufony\DBAL\Query\Builder\Query $query
     * @param int|\DateInterval|null $ttl
     * @return mixed[][]
     */
    public function query(Query $query, int|DateInterval|null $ttl = 60): array
    {
        $context = $query->context();

        // Pre-generate the query string (we'll pass it to the driver later)
        $query_string = $this->driver->generate($query);

        // Determine if query mutates data in the database
        $is_mutation = !($query instanceof Select);

        // For read-only queries, check if the result is cached first
        if ($is_mutation === false && $ttl !== null) {
            // Sorting the context array ensures predictability when dispensing the cache key
            asort($context);
            $cache_key = CacheKeyProvider::get($query_string . serialize($context));
            $cache_result = $this->cache->get($cache_key);

            if ($cache_result !== null) {
                $this->logger->debug("Cache hit for query: $query_string");
                return $cache_result;
            } else {
                $this->logger->debug("Cache miss for query: $query_string");
            }
        }

        // Execute query
        try {
            $query_result = $this->driver->execute($query, $query_string, $context);
        } catch (InvalidArgumentException|QueryException $e) {
            // Log error for query exceptions
            if ($e instanceof QueryException) {
                $this->logger->error("Query failed: $query_string", context: ["exception" => $e]);
            }

            throw $e;
        }

        // Determine the tables that are affected by the query
        $tables = $query->affectedTables();

        if ($is_mutation === false) {
            // Log info for read operations
            $this->logger->info("Query read: $query_string");

            // Cache result
            if ($ttl !== null) {
                $this->cache->set($cache_key, $query_result, ttl: $ttl);
                $this->cache->tag($cache_key, $tables);
                $this->logger->debug("Cache set for query: $query_string");
            }
        } else {
            // Log notice for write operations
            $this->logger->notice("Query mutation: $query_string");

            // Invalidate cache
            $this->cache->invalidateTags($tables);
            $this->logger->debug("Cache clear for tables: " . implode(", ", $tables));
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
     * `\Eufony\DBAL\TransactionException` is thrown.
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
