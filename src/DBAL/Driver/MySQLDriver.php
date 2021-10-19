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

namespace Eufony\DBAL\Driver;

use Eufony\DBAL\Query\Query;

/**
 * Provides a database driver implementation for MySQL using the PDO extension.
 */
class MySQLDriver extends AnsiSQLDriver {

    /**
     * Class constructor.
     * Creates a new connection to the database using the PHP PDO_MySQL
     * extension.
     *
     * Requires the server host, database name, and user credentials to
     * establish the connection.
     *
     * @param string $server
     * @param string $name
     * @param string $user
     * @param string $password
     */
    public function __construct(string $server, string $name, string $user, string $password) {
        parent::__construct("mysql:host=$server;dbname=$name", $user, $password);
    }

    /** @inheritdoc */
    public function generate(Query $query): string {
        // MySQL uses backticks instead of double quotes for identifiers
        return preg_replace("/\"/m", "`", parent::generate($query));
    }

}
