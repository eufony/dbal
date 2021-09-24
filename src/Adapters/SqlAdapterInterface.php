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

namespace Eufony\ORM\Adapters;

/**
 * Provides a common interface for connecting to and querying different
 * database backends.
 *
 * Allows the query logic to be written in generic SQL.
 * The queries are then translated for other database engines and schemas using
 * the `SqlAdapterInterface::translate()` method.
 *
 * @see \Eufony\ORM\Adapters\SqlAdapterInterface::translate()
 */
interface SqlAdapterInterface {

    /**
     * Executes the translated query and returns the result as a PHP array.
     *
     * The original, unmodified query is also passed as a reference.
     * It MAY be used in case some information got lost in translation.
     * It SHOULD NOT be used for re-translation.
     *
     * The array MUST have the same table structure as the original SQL result.
     * Each item in the array MUST correspond to a row in the result table.
     * Each row MUST contain key-value pairs corresponding to single cells.
     * The key name MUST be the same as the column name of the SQL field.
     * The array MUST respect the `ORDER BY` clause in the original SQL query.
     *
     * If the query fails, a `\Eufony\ORM\QueryException` MUST be thrown.
     * If another exception is re-thrown as a `QueryException`, the original
     * exception SHOULD be chained onto the `QueryException` using the
     * `previous` parameter in the exception constructor.
     * The message of the `QueryException` MAY be empty, in which case it will
     * be overridden with a default message.
     *
     * @param string $query
     * @param string $sql
     * @return array<array<mixed>>
     * @throws \Eufony\ORM\QueryException
     */
    public function execute(string $query, string $sql): array;

    /**
     * Translates the given SQL query (written in generic SQL) to the real
     * query to be executed by the database backend.
     *
     * The term "generic SQL" means that the query MUST NOT use features that
     * are exclusive to any specific SQL dialect.
     * The query MUST strictly comply with the ANSI SQL standard syntax.
     *
     * @param string $sql
     * @return string
     *
     * @see https://www.contrib.andrew.cmu.edu/~shadow/sql/sql1992.txt
     */
    public function translate(string $sql): string;

    /**
     * Breaks the connection to the database.
     */
    public function disconnect(): void;

}
