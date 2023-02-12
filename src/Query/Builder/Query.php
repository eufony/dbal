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

namespace Eufony\DBAL\Query\Builder;

use ArrayAccess;
use BadMethodCallException;
use DateInterval;
use Eufony\DBAL\Connection;
use OutOfBoundsException;

/**
 * Provides an abstract base class for all query builders.
 *
 * Provides abstraction away from vendor-specific query language syntax using
 * object-oriented query builders.
 * The query builder representation can then be translated into the correct
 * syntax by a database driver.
 *
 * Allows retrieval of the query builder properties using the array access.
 * However, any modifications of the query properties are not allowed.
 *
 * Cloning a query builder will result in a deep copy of all of its properties.
 */
abstract class Query implements ArrayAccess
{
    /**
     * The context array of the query builder.
     *
     * All values in this array MUST be treated as literal data.
     * The values in any expressions or clauses of the query builder are replaced
     * by random 32-character alphanumeric named parameters.
     *
     * @var mixed[] $context
     */
    protected array $context = [];

    /**
     * Private class constructor.
     * Query builders cannot be initialized using the `new` syntax.
     */
    protected function __construct()
    {
    }

    /**
     * Magic method for cloning the object.
     * Ensures that cloning a query builder creates a deep copy of all of its
     * properties.
     */
    public function __clone(): void
    {
        unserialize(serialize($this));
    }

    /**
     * Executes this query in the given database connection.
     *
     * If no database key is given, defaults to the default connection.
     * Optionally allows setting the expiration time of the query's cached result.
     *
     * @param string|null $database
     * @param int|\DateInterval|null $ttl
     * @return mixed[][]
     */
    public function execute(?string $database = null, int|DateInterval|null $ttl = 60): array
    {
        $connection = Connection::get($database);
        return $connection->query($this, $ttl);
    }

    /**
     * Getter for the context array.
     *
     * @return mixed[]
     */
    public function context(): array
    {
        return $this->context;
    }

    /**
     * Returns a list of tables that are either read from or written to by this
     * query.
     *
     * @return string[]
     */
    abstract public function affectedTables(): array;

    /**
     * @inheritDoc
     */
    public function offsetExists(mixed $offset): bool
    {
        return property_exists($this, $offset) && isset($this->$offset);
    }

    /**
     * @inheritDoc
     */
    public function offsetGet(mixed $offset): mixed
    {
        // Ensure property exists
        if (!$this->offsetExists($offset)) {
            throw new OutOfBoundsException("Unknown query builder property");
        }

        return $this->$offset;
    }

    /**
     * @inheritDoc
     */
    public function offsetSet(mixed $offset, mixed $value): void
    {
        throw new BadMethodCallException("Query builder properties are read-only");
    }

    /**
     * @inheritDoc
     */
    public function offsetUnset(mixed $offset): void
    {
        throw new BadMethodCallException("Query builder properties are read-only");
    }
}
