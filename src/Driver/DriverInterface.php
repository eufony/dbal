<?php
/*
 * The Eufony DBAL Package
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

namespace Eufony\DBAL\Driver;

use Eufony\DBAL\Query\Builder\Query;

/**
 * Provides a common interface for connecting to and querying different
 * database backends.
 *
 * The connection to the database MUST be kept alive for as long as the
 * lifetime of the driver instance.
 *
 * If the database requires specific instructions to finish a session, these
 * instructions SHOULD be issued in the destructor of the object class.
 */
interface DriverInterface
{
    /**
     * Generates the query string to be executed from the given query builder.
     *
     * @param \Eufony\DBAL\Query\Builder\Query $query
     * @return string
     */
    public function generate(Query $query): string;

    /**
     * Executes the pre-generated query and returns the result as a PHP array.
     * Also requires the original, unmodified query builder.
     *
     * The array MUST return each numerically indexed row as a nested array,
     * indexed by the field name as returned by the result set.
     *
     * The query MAY contain positional (`?`) or named (`:foo`) parameters
     * (exclusively), which MUST be passed in through the context array.
     * The values in the context array MUST be treated as literal data, the
     * database MUST NOT interpret them as part of the query.
     * If the database provides functionality for prepared statements, taking
     * advantage of it is highly RECOMMENDED.
     *
     * If the query mixes both positional and named parameters, or if the keys in
     * the context array don't match the parameters in the query, an
     * `\InvalidArgumentException` MUST be thrown.
     *
     * If the query fails, a `\Eufony\DBAL\QueryException` MUST be thrown.
     * If another exception is re-thrown as a `QueryException`, the original
     * exception SHOULD be chained onto the `QueryException` using the `previous`
     * parameter in the exception constructor.
     *
     * @param \Eufony\DBAL\Query\Builder\Query $query
     * @param string $query_string
     * @param mixed[] $context
     * @return mixed[][]
     */
    public function execute(Query $query, string $query_string, array $context): array;

    /**
     * Checks whether the database is currently in a transaction.
     *
     * Returns `true` if a transaction is active, `false` otherwise.
     *
     * @return bool
     */
    public function inTransaction(): bool;

    /**
     * Initiates a transaction.
     *
     * While in a transaction, any modifications to the database MUST be buffered
     * until either `commit()` or `rollback()` is called.
     *
     * Transactions cannot be nested.
     * If this method is called when a transaction is already active, a
     * `\BadMethodCallException` MUST be thrown.
     */
    public function beginTransaction(): void;

    /**
     * Commits a transaction.
     *
     * Previously buffered modifications to the database MUST be applied
     * immediately.
     *
     * If this method is called when a transaction is not active a
     * `\BadMethodCallException` MUST be thrown.
     */
    public function commit(): void;

    /**
     * Rolls back a transaction.
     *
     * Previously buffered modifications to the database MUST be discarded.
     *
     * If this method is called when a transaction is not active a
     * `\BadMethodCallException` MUST be thrown.
     */
    public function rollback(): void;
}
