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

use DateInterval;
use Eufony\DBAL\Driver\DriverInterface;
use Eufony\DBAL\Query\Query;
use Eufony\DBAL\Query\Select;
use Eufony\ORM\InvalidArgumentException;
use Eufony\ORM\ORM;
use Eufony\ORM\QueryException;
use ReflectionObject;
use Throwable;

/**
 * Represents a connection to a database.
 * Provides methods to send queries to the database engine.
 */
class Connection {

    /**
     * A backend driver for building and executing queries.
     *
     * @var \Eufony\DBAL\Driver\DriverInterface $driver
     */
    private DriverInterface $driver;

    /**
     * Class constructor.
     * Creates a new connection to a database engine using the given driver
     * backend.
     *
     * @param \Eufony\DBAL\Driver\DriverInterface $driver
     */
    public function __construct(DriverInterface $driver) {
        $this->driver = $driver;
    }

    /**
     * Returns the current database driver.
     *
     * @return \Eufony\DBAL\Driver\DriverInterface
     */
    public function driver(): DriverInterface {
        return $this->driver;
    }

    /**
     * Executes the given query.
     *
     * Additionally handles caching (for read-only queries), logging, and
     * generation of the query string from a query builder.
     *
     * The query, whether passed in as a string directly or built using a query
     * builder, is passed to the `DriverInterface::execute()` method along with
     * the context array, providing easy protection against SQL injection
     * attacks.
     *
     * The cached result's expiration can be set using the `$ttl` parameter;
     * either as a `DateInterval` object or an integer number of minutes.
     * Defaults to 1 minute.
     *
     * Throws a `\Eufony\ORM\QueryException` on failure.
     *
     * @param string|\Eufony\DBAL\Query\Query $query
     * @param array<mixed> $context
     * @param DateInterval|int $ttl
     * @return array<array<mixed>>
     * @throws \Eufony\ORM\QueryException
     *
     * @see \Eufony\DBAL\Driver\DriverInterface::execute()
     */
    public function query(string|Query $query, array $context = [], DateInterval|int $ttl = 1): array {
        // Fetch logging and cache implementations
        $logger = ORM::logger();
        $cache = ORM::cache();

        // If a query builder is passed, determine if it mutates data in the database
        // Otherwise, cannot determine if the string is a mutation, set to null
        /** @var bool|null $is_mutation */
        $is_mutation = $query instanceof Query ? get_class($query) === Select::class : null;

        // If the query was built using a query builder, generate the query string
        $query_string = $query instanceof Query ? $this->driver->generate($query) : $query;

        // For read-only queries, check if the result is cached first
        if ($is_mutation === false) {
            // Hashing the query along with the context array ensures the cache
            // key matches PSR-16 standards on the valid character set and
            // maximum supported length
            // Sorting the context array ensures predictability when hashing
            asort($context);
            $cache_key = hash("sha256", $query_string . implode("|", $context));
            $cache_result = $cache->get($cache_key);

            if ($cache_result !== null) {
                $logger->debug("Query cache hit: $query_string");
                return $cache_result;
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
                $logger->error($message, context: ["exception" => $e]);
            }

            throw $e;
        }

        if ($is_mutation === true) {
            // Log notice for write operations
            $logger->notice("Query write op: $query_string");

            // Invalidate cache
            // TODO: Don't need to invalidate the entire cache, only the tables that were altered
            $cache->clear();
            $logger->debug("Clear cache");
        } elseif ($is_mutation === false) {
            // Log info for read operations
            $logger->info("Query read op: $query_string");

            // Cache result
            $cache->set($cache_key, $query_result, ttl: $ttl);
            $logger->debug("Query cached result: $query_string");
        } else {
            // Log notice of unknown operations
            $logger->notice("Query (unknown type): $query_string");

            // Invalidate the entire cache, just to be safe
            $cache->clear();
            $logger->debug("Clear cache");
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
     * @throws \Throwable
     */
    public function transactional(callable $callback): void {
        // Fetch logging implementation
        $logger = ORM::logger();

        // Check for nested transactions
        // Only the root transaction issues calls to begin transactions, commits, and rollbacks
        $is_root_transaction = !$this->driver->inTransaction();

        if ($is_root_transaction) {
            // Start transaction
            $logger->debug("Start transaction");
            $this->driver->beginTransaction();
        }

        try {
            // Call callback function
            call_user_func_array($callback, [$this]);
        } catch (Throwable $e) {
            if ($is_root_transaction) {
                // Transaction failed, roll back
                $this->driver->rollback();
                $logger->error("Transaction failed, roll back", context: ["exception" => $e]);
            }

            // Propagate error
            throw $e;
        }

        if ($is_root_transaction) {
            // Transaction didn't throw errors, commit to database
            $this->driver->commit();
            $logger->debug("Commit transaction");
        }
    }

}
