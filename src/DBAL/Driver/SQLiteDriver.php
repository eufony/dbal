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

namespace Eufony\ORM\DBAL\Driver;

use Eufony\ORM\DBAL\Query\Builder\Query;

/**
 * Provides a database driver implementation for SQLite using the PDO
 * extension.
 */
class SQLiteDriver extends AnsiSQLDriver
{
    /**
     * Class constructor.
     * Creates a new connection to the database using the PHP `pdo_sqlite`
     * extension.
     *
     * Requires the file path of the database file to establish the connection.
     * If the path is `:memory:`, an in-memory database will be created.
     * It will be destroyed at the end of the PHP process.
     *
     * @param string $path
     */
    public function __construct(string $path)
    {
        parent::__construct("sqlite:" . $path);
    }

    /**
     * @inheritDoc
     */
    protected function generateLimitClause(Query $query): string
    {
        // Get query props
        $limit = $query['limit'] ?? null;
        $offset = $query['offset'] ?? null;

        // Return empty string if no limit set
        if (!isset($limit)) {
            return "";
        }

        // Build limit
        $clause = " LIMIT $limit";

        // Build offset
        if (isset($offset)) {
            $clause .= " OFFSET $offset";
        }

        // Return result
        return $clause;
    }
}
