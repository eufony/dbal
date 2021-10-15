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

use Eufony\ORM\BadMethodCallException;
use Eufony\ORM\QueryException;
use PDO;
use PDOException;

/**
 * Provides common functionality for implementing database drivers using the
 * PHP PDO extension.
 *
 * To use this class, declare it in a `use` statement and call the `connect()`
 * method in the class constructor.
 *
 * **Notice:** Driver implementations using this trait will require `ext-pdo`
 * as well as their specific PDO drivers to be installed and enabled.
 */
trait PDODriverTrait {

    /**
     * The PDO object used internally to interface with SQL databases.
     *
     * @var \PDO $pdo
     */
    private PDO $pdo;

    /**
     * Returns the internal PDO object.
     *
     * @return \PDO
     */
    public function pdo(): PDO {
        return $this->pdo;
    }

    /**
     * Creates a new connection to the database.
     *
     * The given parameters are passed directly to the underlying PDO object.
     * Refer to the official PDO documentation for more details.
     *
     * @param string $dsn
     * @param string|null $user
     * @param string|null $password
     *
     * @see https://www.php.net/manual/en/pdo.construct.php
     */
    public function connect(string $dsn, ?string $user = null, ?string $password = null) {
        $this->pdo = new PDO($dsn, $user, $password);
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    /** @inheritdoc */
    public function execute(string $query, array $context): array {
        try {
            // Prepare statement from the given query
            $statement = $this->pdo->prepare($query);

            // Execute prepared statement with the given context array
            $statement->execute($context);
        } catch (PDOException $e) {
            // TODO: MUST throw an InvalidArgumentException if the placeholders are invalid / mismatched.
            throw new QueryException(previous: $e);
        }

        // Return result as an associative array
        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    /** @inheritdoc */
    public function inTransaction(): bool {
        return $this->pdo->inTransaction();
    }

    /** @inheritdoc */
    public function beginTransaction(): void {
        if ($this->inTransaction()) {
            throw new BadMethodCallException("A transaction is already active");
        }

        $this->pdo->beginTransaction();
    }

    /** @inheritdoc */
    public function commit(): void {
        if (!$this->inTransaction()) {
            throw new BadMethodCallException("No transaction to commit");
        }

        $this->pdo->commit();
    }

    /** @inheritdoc */
    public function rollback(): void {
        if (!$this->inTransaction()) {
            throw new BadMethodCallException("No transaction to roll back to");
        }

        $this->pdo->rollBack();
    }

}
