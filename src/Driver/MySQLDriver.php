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
 * Provides a database driver implementation for MySQL using the PDO extension.
 */
class MySQLDriver extends AnsiSQLDriver
{
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

    /**
     * @inheritDoc
     */
    protected function quoteField(string $field): string
    {
        return preg_replace("/\"/", "`", parent::quoteField($field));
    }
}
