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

use Eufony\ORM\DBAL\Query\Create;
use Eufony\ORM\DBAL\Query\Delete;
use Eufony\ORM\DBAL\Query\Drop;
use Eufony\ORM\DBAL\Query\Insert;
use Eufony\ORM\DBAL\Query\Query;
use Eufony\ORM\DBAL\Query\Select;
use Eufony\ORM\DBAL\Query\Update;
use ReflectionClass;

/**
 * Provides an abstract database driver implementation that other drivers can
 * inherit from.
 *
 * Delegates the `generate()` method to other methods that correspond to each
 * of the query builders.
 * This reduces the boilerplate code that a driver has to implement to check
 * for the types of the queries.
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
        $short_name = (new ReflectionClass(get_class($query)))->getShortName();
        return $this->$short_name($query);
    }

    /**
     * Generates the SQL query to be executed from a `Select` query builder.
     *
     * @param \Eufony\ORM\DBAL\Query\Select $query
     * @return string
     */
    abstract protected function select(Select $query): string;

    /**
     * Generates the SQL query to be executed from an `Insert` query builder.
     *
     * @param \Eufony\ORM\DBAL\Query\Insert $query
     * @return string
     */
    abstract protected function insert(Insert $query): string;

    /**
     * Generates the SQL query to be executed from an `Update` query builder.
     *
     * @param \Eufony\ORM\DBAL\Query\Update $query
     * @return string
     */
    abstract protected function update(Update $query): string;

    /**
     * Generates the SQL query to be executed from a `Delete` query builder.
     *
     * @param \Eufony\ORM\DBAL\Query\Delete $query
     * @return string
     */
    abstract protected function delete(Delete $query): string;

    /**
     * Generates the SQL query to be executed from a `Create` query builder.
     *
     * @param \Eufony\ORM\DBAL\Query\Create $query
     * @return string
     */
    abstract protected function create(Create $query): string;

    /**
     * Generates the SQL query to be executed from a `Drop` query builder.
     *
     * @param \Eufony\ORM\DBAL\Query\Drop $query
     * @return string
     */
    abstract protected function drop(Drop $query): string;

}
