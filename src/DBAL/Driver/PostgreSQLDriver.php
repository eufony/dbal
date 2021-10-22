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

namespace Eufony\ORM\DBAL\Driver;

/**
 * Provides a database driver implementation for PostgreSQL using the PDO
 * extension.
 */
class PostgreSQLDriver extends AnsiSQLDriver {

    /**
     * Class constructor.
     * Creates a new connection to the database using the PHP PDO_PostgreSQL
     * extension.
     *
     * Requires the server host and port, database name, and user credentials
     * to establish the connection.
     *
     * @param string $server
     * @param int $port
     * @param string $name
     * @param string $user
     * @param string $password
     */
    public function __construct(string $server, int $port, string $name, string $user, string $password) {
        parent::__construct("pgsql:host=$server;port=$port;dbname=$name;user=$user;password=$password");
    }

}
