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

namespace Eufony\DBAL\Drivers;

use Eufony\DBAL\Queries\Query;

/**
 * Provides a common interface for connecting to and querying different
 * database backends.
 *
 * Allows the query logic to be written using the query builders in the
 * `\Eufony\DBAL\Queries` namespace.
 * The implementations of this interface then generate the query string for
 * their own vendor-specific database backends using the `generate()` method.
 *
 * The `\Eufony\DBAL\Drivers\AbstractDatabaseDriver` class provides some
 * boilerplate code to ease the implementation of this interface.
 *
 * The connection to the database MUST be kept alive for as long as the
 * lifetime of the driver instance.
 * If the database requires specific instructions to finish a session, these
 * instructions SHOULD be issued in the destructor of the object class.
 *
 * @see \Eufony\DBAL\Queries\Query
 * @see \Eufony\DBAL\Drivers\AbstractDatabaseDriver
 */
interface DatabaseDriverInterface {

    /**
     * Generates the query string to be executed from a query builder.
     *
     * @param Query $query
     * @return string
     */
    public function generate(Query $query): string;

    /**
     * Executes the generated query and returns the result as a PHP array.
     *
     * The array MUST return each numerically indexed row as a nested array,
     * indexed by the column name as returned by the result set.
     * This matches the behaviour of `\PDO::FETCH_ASSOC`.
     *
     * The query MAY contain positional (`?`) or named (`:foo`) parameters
     * (exclusively), which are passed in through `$context`.
     * The placeholders in the query MUST be substituted for their
     * corresponding values in the context array before execution.
     *
     * **Warning:** The values in the context array MUST be treated as literal
     * data, the database MUST NOT interpret them as part of the query.
     * The values in the array MUST be escaped properly for use in the query.
     * If the database provides functionality for prepared statements, taking
     * advantage of them is highly RECOMMENDED.
     *
     * If the query mixes both positional and named parameters, or if the
     * keys in the context array don't match the parameters in the query, a
     * `\Eufony\DBAL\InvalidArgumentException` MUST be thrown.
     * The message of the `InvalidArgumentException` MAY be empty, in which
     * case it is overridden with a default message.
     *
     * If the query fails, a `\Eufony\DBAL\QueryException` MUST be thrown.
     * If another exception is re-thrown as a `QueryException`, the original
     * exception SHOULD be chained onto the `QueryException` using the
     * `previous` parameter in the exception constructor.
     * The message of the `QueryException` MAY be empty, in which case it is
     * overridden with a default message.
     *
     * @param string $query
     * @param array $context
     * @return array<array<mixed>>
     * @throws \Eufony\DBAL\InvalidArgumentException
     * @throws \Eufony\DBAL\QueryException
     */
    public function execute(string $query, array $context): array;

    /**
     * Checks whether the database is currently in a transaction.
     * Returns `true` if a transaction is active, `false` otherwise.
     *
     * @return bool
     */
    public function inTransaction(): bool;

    /**
     * Initiates a transaction.
     *
     * While in a transaction, any modifications to the database MUST be
     * buffered until either `commit()` or `rollback()` is called.
     *
     * Transactions cannot be nested.
     * If this method is called when a transaction is already active a
     * `\Eufony\DBAL\BadMethodCallException` MUST be thrown.
     *
     * @throws \Eufony\DBAL\BadMethodCallException
     */
    public function beginTransaction(): void;

    /**
     * Commits a transaction.
     *
     * Buffered modifications to the database MUST be applied.
     *
     * If this method is called when a transaction is not active a
     * `\Eufony\DBAL\BadMethodCallException` MUST be thrown.
     *
     * @throws \Eufony\DBAL\BadMethodCallException
     */
    public function commit(): void;

    /**
     * Rolls back a transaction
     *
     * Buffered modifications to the database MUST be discarded.
     *
     * If this method is called when a transaction is not active a
     * `\Eufony\DBAL\BadMethodCallException` MUST be thrown.
     *
     * @throws \Eufony\DBAL\BadMethodCallException
     */
    public function rollback(): void;

}
