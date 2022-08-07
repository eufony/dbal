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

namespace Eufony\DBAL\Driver;

use Eufony\DBAL\Query\Builder\Query;
use Generator;

/**
 * Provides a common interface for connecting to and querying different
 * database backends.
 *
 * The connection to the database is kept alive for as long as the lifetime of
 * the driver instance.
 */
interface DriverInterface
{
    /**
     * Generates then executes the query string from the given query builder.
     *
     * Returns a `Generator` object that yields exactly 2 values:
     *
     * The first value is the flavor-specific query string, exactly as it will be
     * executed in the database.
     *
     * The second value is the result set of the query builder as a PHP array.
     * The array returns each numerically indexed row as a nested array, indexed by
     * the field name as returned by the result set.
     *
     * The query string is yielded before it is executed in the database.
     *
     * The result of the query string might not match the yielded value from this
     * method exactly, as features that are unsupported by the database backend
     * will be emulated in PHP.
     *
     * @param \Eufony\DBAL\Query\Builder\Query $query
     * @return Generator
     */
    public function query(Query $query): Generator;

    /**
     * Executes the given query string and returns the result as a PHP array.
     *
     * The array returns each numerically indexed row as a nested array, indexed by
     * the field name as returned by the result set.
     *
     * The query may contain positional (`?`) or named (`:foo`) parameters
     * (exclusively), whose values can be passed in through the context array.
     * The values in the context array are treated as literal data, they
     * are not interpreted as being part of the query.
     *
     * @param string $query
     * @param mixed[] $context
     * @return mixed[][]
     *
     * @internal Executing SQL queries directly defeats the purpose of the database
     * abstraction layer. Use an appropriate query builder instead.
     */
    public function execute(string $query, array $context = []): array;

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
     * While in a transaction, any modifications to the database are buffered until
     * either `commit()` or `rollback()` is called.
     *
     * Transactions cannot be nested.
     */
    public function beginTransaction(): void;

    /**
     * Commits a transaction.
     *
     * Immediately applies previously buffered modifications to the database.
     */
    public function commit(): void;

    /**
     * Rolls back a transaction.
     *
     * Discards previously buffered modifications to the database.
     */
    public function rollback(): void;
}
