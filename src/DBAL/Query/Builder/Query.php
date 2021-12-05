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

namespace Eufony\ORM\DBAL\Query\Builder;

use ArrayAccess;
use BadMethodCallException;
use DateInterval;
use Eufony\ORM\DBAL\Connection;
use OutOfBoundsException;

/**
 * Provides an abstract base class for all query builders.
 *
 * Provides abstraction away from vendor-specific query language syntax using
 * object-oriented query builders.
 * The query builder representation can then be translated back into the
 * correct syntax by a database driver.
 *
 * Allows retrieval of the query builder properties using the array access.
 * However, does not allow modification of any query properties.
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
     * @var array $context
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
     * Executes this query in a given database connection.
     *
     * If no database key is given, defaults to the default connection.
     * Optionally allows setting the expiration time of the query's cached result.
     *
     * @param string|null $database
     * @param int|\DateInterval|null $ttl
     * @return mixed[][]
     */
    public function execute(?string $database = null, int|DateInterval|null $ttl = 1): array
    {
        $connection = Connection::get($database);
        $query_string = $connection->driver()->generate($this);
        return $connection->query($query_string, $this['context'], $ttl);
    }

    /**
     * @inheritDoc
     */
    public function offsetExists($offset): bool
    {
        return property_exists($this, $offset) && isset($this->$offset);
    }

    /**
     * @inheritDoc
     */
    public function offsetGet($offset)
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
    public function offsetSet($offset, $value)
    {
        throw new BadMethodCallException("Query builder properties are read-only");
    }

    /**
     * @inheritDoc
     */
    public function offsetUnset($offset)
    {
        throw new BadMethodCallException("Query builder properties are read-only");
    }
}
