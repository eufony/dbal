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

namespace Eufony\ORM\DBAL\Driver;

use Eufony\ORM\DBAL\Query\Builder\Create;
use Eufony\ORM\DBAL\Query\Builder\Delete;
use Eufony\ORM\DBAL\Query\Builder\Drop;
use Eufony\ORM\DBAL\Query\Builder\Insert;
use Eufony\ORM\DBAL\Query\Builder\Query;
use Eufony\ORM\DBAL\Query\Builder\Select;
use Eufony\ORM\DBAL\Query\Builder\Update;
use Eufony\ORM\DBAL\Query\Expr;
use ReflectionClass;

/**
 * Provides an abstract database driver implementation that other drivers can
 * inherit from.
 *
 * Delegates the `generate()` method to other methods that correspond to each
 * of the query builders.
 * This reduces the boilerplate code that a driver has to implement to check
 * for the types of the queries.
 *
 * Additionally defines abstract methods to generate the query strings of an
 * expression and each of the query clauses.
 */
abstract class AbstractDriver implements DriverInterface
{
    /**
     * Class constructor.
     * Creates a new connection to the database.
     */
    public function __construct()
    {
    }

    /**
     * Class destructor.
     * Breaks the connection to the database.
     */
    public function __destruct()
    {
    }

    /**
     * @inheritDoc
     */
    public function generate(Query $query): string
    {
        $short_name = (new ReflectionClass(get_class($query)))->getShortName();
        $method_name = "generate" . ucfirst($short_name);
        return $this->$method_name($query);
    }

    /**
     * Generates the query string to be executed from a `Select` query builder.
     *
     * @param \Eufony\ORM\DBAL\Query\Builder\Select $query
     * @return string
     */
    abstract protected function generateSelect(Select $query): string;

    /**
     * Generates the query string to be executed from an `Insert` query builder.
     *
     * @param \Eufony\ORM\DBAL\Query\Builder\Insert $query
     * @return string
     */
    abstract protected function generateInsert(Insert $query): string;

    /**
     * Generates the query string to be executed from an `Update` query builder.
     *
     * @param \Eufony\ORM\DBAL\Query\Builder\Update $query
     * @return string
     */
    abstract protected function generateUpdate(Update $query): string;

    /**
     * Generates the query string to be executed from a `Delete` query builder.
     *
     * @param \Eufony\ORM\DBAL\Query\Builder\Delete $query
     * @return string
     */
    abstract protected function generateDelete(Delete $query): string;

    /**
     * Generates the query string to be executed from a `Create` query builder.
     *
     * @param \Eufony\ORM\DBAL\Query\Builder\Create $query
     * @return string
     */
    abstract protected function generateCreate(Create $query): string;

    /**
     * Generates the query string to be executed from a `Drop` query builder.
     *
     * @param \Eufony\ORM\DBAL\Query\Builder\Drop $query
     * @return string
     */
    abstract protected function generateDrop(Drop $query): string;

    /**
     * Generates the `GROUP BY` clause of a query string.
     *
     * @param \Eufony\ORM\DBAL\Query\Builder\Query $query
     * @return string
     */
    abstract protected function generateGroupByClause(Query $query): string;

    /**
     * Generates the `JOIN` clauses of a query string.
     *
     * @param \Eufony\ORM\DBAL\Query\Builder\Query $query
     * @return string
     */
    abstract protected function generateJoinClause(Query $query): string;

    /**
     * Generates the `LIMIT` and `OFFSET` clause of a query string.
     *
     * @param \Eufony\ORM\DBAL\Query\Builder\Query $query
     * @return string
     */
    abstract protected function generateLimitClause(Query $query): string;

    /**
     * Generates the `ORDER BY` clause of a query string.
     *
     * @param \Eufony\ORM\DBAL\Query\Builder\Query $query
     * @return string
     */
    abstract protected function generateOrderByClause(Query $query): string;

    /**
     * Generates the `VALUES` clause of a query string.
     *
     * @param \Eufony\ORM\DBAL\Query\Builder\Query $query
     * @return string
     */
    abstract protected function generateValuesClause(Query $query): string;

    /**
     * Generates the `WHERE` clause of a query string.
     *
     * @param \Eufony\ORM\DBAL\Query\Builder\Query $query
     * @return string
     */
    abstract protected function generateWhereClause(Query $query): string;

    /**
     * Recursively generates the query string of an expression.
     *
     * @param \Eufony\ORM\DBAL\Query\Expr $expr
     * @return string
     */
    abstract protected function generateExpression(Expr $expr): string;
}
