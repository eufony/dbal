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

/**
 * Provides a database driver implementation for SQLite using the PDO
 * extension.
 * Currently supports SQLite version 3.
 *
 * **Notice:** This class requires `ext-pdo` as well as `ext-pdo_sqlite` to be
 * installed and enabled.
 */
class SQLiteDriver extends AnsiSqlDriver {

    /**
     * Class constructor.
     * Creates a new connection to the SQLite database with the file path
     * `$path`.
     *
     * @param string $path
     */
    public function __construct(string $path) {
        parent::__construct("sqlite:" . $path);
    }

}
