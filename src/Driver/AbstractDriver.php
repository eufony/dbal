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

use BadMethodCallException;
use Eufony\DBAL\QueryException;
use PDO;
use PDOException;
use SensitiveParameter;

/**
 * Provides an abstract database driver implementation that other drivers can
 * inherit from.
 *
 * Uses the PHP PDO extension to interface with the database.
 * Implements the all transaction-related methods in the * `DriverInterface`.
 * Inheriting classes only need to implement the `query()` method.
 *
 * Also implements an `execute()` method for executing an SQL query string and
 * returning the result as a PHP array.
 */
abstract class AbstractDriver implements DriverInterface
{
    /**
     * The PDO object used internally to interface with SQL databases.
     *
     * @var \PDO $pdo
     */
    protected PDO $pdo;

    /**
     * Class constructor.
     * Creates a new connection to the database.
     *
     * Uses the PHP PDO extension for interfacing with the database.
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
    public function __construct(string $dsn, ?string $user = null, #[SensitiveParameter] ?string $password = null)
    {
        $this->pdo = new PDO($dsn, $user, $password);
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    /**
     * Class destructor.
     * Breaks the connection to the database.
     */
    public function __destruct()
    {
    }

    /**
     * Executes the given query string and returns the result as a PHP array.
     *
     * The array returns each numerically indexed row as a nested array, indexed by
     * the field name as returned by the result set.
     *
     * The query may contain positional (`?`) or named (`:foo`) parameters
     * (exclusively), whose values can be passed in through the context array.
     * The values in the context array are treated as literal data, they
     * are not interpreted as part of the query.
     *
     * @param string $query
     * @param mixed[] $context
     * @return mixed[][]
     *
     * @internal Executing SQL queries directly defeats the purpose of the database
     * abstraction layer. Use an appropriate query builder instead.
     */
    public function execute(string $query, array $context = []): array
    {
        try {
            // Prepare statement from the given query
            $statement = $this->pdo->prepare($query);

            // Execute prepared statement with the given context array
            $statement->execute($context);
        } catch (PDOException $e) {
            // TODO: MUST throw an InvalidArgumentException if the placeholders are invalid / mismatched.
            if (str_starts_with($e->getMessage(), 'SQLSTATE[')) {
                preg_match("/SQLSTATE\[\w+\]: ([\w ]+): \w+ (.*)/", $e->getMessage(), $matches);
                $message = $matches[1] . ": " . ucfirst($matches[2]);
            }

            throw new QueryException($message ?? "", previous: $e);
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
