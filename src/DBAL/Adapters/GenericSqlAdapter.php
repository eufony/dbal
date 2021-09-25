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

namespace Eufony\ORM\DBAL\Adapters;

use Eufony\ORM\QueryException;

/**
 * Provides a simple backend for generic SQL queries using the PHP PDO class.
 * The queries passed to this adapter are returned "as-is", without any actual
 * translation.
 *
 * Notice: This class requires the PDO extension as well as the specific PDO
 * drivers to be installed.
 */
class GenericSqlAdapter implements SqlAdapterInterface {

    /**
     * The PDO object used internally to interface with SQL databases.
     * Set to `null` when disconnected from the database.
     *
     * @var \PDO|null $pdo
     */
    private \PDO|null $pdo;

    /**
     * Class constructor.
     * The given parameters are passed directly to the underlying PDO object.
     * Refer to the official PDO documentation for more details.
     *
     * @param string $dsn
     * @param string|null $user
     * @param string|null $password
     *
     * @see https://www.php.net/manual/en/pdo.construct.php
     */
    public function __construct(string $dsn, ?string $user = null, ?string $password = null) {
        $this->pdo = new \PDO($dsn, $user, $password);
        $this->pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
    }

    /**
     * Returns the internal PDO object, or `null` if the connection has been
     * broken.
     *
     * @return \PDO|null
     */
    public function pdo(): \PDO|null {
        return $this->pdo;
    }

    /** @inheritdoc */
    public function execute(string $query, string $sql): array {
        try {
            $statement = $this->pdo->query($query);
            return $statement->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            throw new QueryException(previous: $e);
        }
    }

    /** @inheritdoc */
    public function translate(string $sql): string {
        return $sql;
    }

    /** @inheritdoc */
    public function disconnect(): void {
        $this->pdo = null;
    }

}
