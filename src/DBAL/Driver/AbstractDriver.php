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

use Eufony\DBAL\Query\Create;
use Eufony\DBAL\Query\Delete;
use Eufony\DBAL\Query\Drop;
use Eufony\DBAL\Query\Insert;
use Eufony\DBAL\Query\Query;
use Eufony\DBAL\Query\Select;
use Eufony\DBAL\Query\Update;
use Eufony\ORM\InvalidArgumentException;


/**
 * Provides an abstract database driver implementation that other drivers can
 * inherit from.
 *
 * Delegates the `generate()` method to other methods that correspond to each
 * of the query builders.
 * This reduces the boilerplate code that a driver has to implement to check
 * for the type of query passed.
 */
abstract class AbstractDriver implements DriverInterface {

    /**
     * Class constructor.
     * Creates a new connection to the database.
     */
    public function __construct() {
    }

    /**
     * Class destructor.
     * Breaks the connection to the database.
     */
    public function __destruct() {
    }

    /** @inheritdoc */
    public function generate(Query $query): string {
        return match (get_class($query)) {
            Select::class => $this->select($query),
            Insert::class => $this->insert($query),
            Update::class => $this->update($query),
            Delete::class => $this->delete($query),
            Create::class => $this->create($query),
            Drop::class => $this->drop($query),
            default => throw new InvalidArgumentException("Unknown query type: " . get_class($query))
        };
    }

    /**
     * Generates the SQL query to be executed from the `Select` query builder.
     *
     * @param \Eufony\DBAL\Query\Select $query
     * @return string
     */
    abstract protected function select(Select $query): string;

    /**
     * Generates the SQL query to be executed from the `Insert` query builder.
     *
     * @param \Eufony\DBAL\Query\Insert $query
     * @return string
     */
    abstract protected function insert(Insert $query): string;

    /**
     * Generates the SQL query to be executed from the `Update` query builder.
     *
     * @param \Eufony\DBAL\Query\Update $query
     * @return string
     */
    abstract protected function update(Update $query): string;

    /**
     * Generates the SQL query to be executed from the `Delete` query builder.
     *
     * @param \Eufony\DBAL\Query\Delete $query
     * @return string
     */
    abstract protected function delete(Delete $query): string;

    /**
     * Generates the SQL query to be executed from the `Create` query builder.
     *
     * @param \Eufony\DBAL\Query\Create $query
     * @return string
     */
    abstract protected function create(Create $query): string;

    /**
     * Generates the SQL query to be executed from the `Drop` query builder.
     *
     * @param \Eufony\DBAL\Query\Drop $query
     * @return string
     */
    abstract protected function drop(Drop $query): string;

}
