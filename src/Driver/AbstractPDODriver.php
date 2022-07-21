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

use BadMethodCallException;
use Eufony\DBAL\QueryException;
use PDO;
use PDOException;

/**
 * Provides an abstract database driver implementation that other drivers can
 * inherit from.
 *
 * Uses the PHP PDO extension to implement most of the methods in the
 * `DriverInterface`.
 * Subclasses of this class only need to implement the `generate()` method.
 *
 * Inherits from the `AbstractDriver` class to delegate the `generate()` method
 * correspond methods for each of the query builders.
 */
abstract class AbstractPDODriver extends AbstractDriver
{
    /**
     * The PDO object used internally to interface with SQL databases.
     *
     * @var \PDO $pdo
     */
    protected PDO $pdo;

    /**
     * {@inheritDoc}
     *
     * The given parameters are passed directly to the underlying PDO object.
     * Refer to the official PDO documentation for more details.
     *
     * @see https://www.php.net/manual/en/pdo.construct.php
     *
     * @param string $dsn
     * @param string|null $user
     * @param string|null $password
     */
    public function __construct(string $dsn, ?string $user = null, ?string $password = null)
    {
        parent::__construct();
        $this->pdo = new PDO($dsn, $user, $password);
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    /**
     * @inheritDoc
     */
    public function execute(string $query, array $context): array
    {
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

    /**
     * @inheritDoc
     */
    public function inTransaction(): bool
    {
        return $this->pdo->inTransaction();
    }

    /**
     * @inheritDoc
     */
    public function beginTransaction(): void
    {
        if ($this->inTransaction()) {
            throw new BadMethodCallException("A transaction is already active");
        }

        $this->pdo->beginTransaction();
    }

    /**
     * @inheritDoc
     */
    public function commit(): void
    {
        if (!$this->inTransaction()) {
            throw new BadMethodCallException("No transaction to commit");
        }

        $this->pdo->commit();
    }

    /**
     * @inheritDoc
     */
    public function rollback(): void
    {
        if (!$this->inTransaction()) {
            throw new BadMethodCallException("No transaction to roll back to");
        }

        $this->pdo->rollBack();
    }
}