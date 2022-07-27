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

namespace Eufony\DBAL\Query\Clause;

use Eufony\DBAL\Query\Expr;

/**
 * Provides properties and methods for query builders that support the `GROUP
 * BY` and `HAVING` clauses.
 */
trait GroupByClauseTrait
{
    /**
     * An array of field names to group the query results by.
     *
     * @var string[] $groupBy
     */
    protected array $groupBy;

    /**
     * An optional expression to filter the selected groups.
     *
     * @var Expr $having
     */
    protected Expr $having;

    /**
     * Sets the field names to group the query results by.
     *
     * @param string[] $fields
     * @return $this
     */
    public function groupBy(string ...$fields): static
    {
        $this->groupBy = $fields;
        return $this;
    }

    /**
     * Sets the expression to filter the selected groups.
     *
     * Must be used in conjunction with the `groupBy()` method.
     *
     * @param \Eufony\DBAL\Query\Expr $expr
     * @return $this
     */
    public function having(Expr $expr): static
    {
        $this->having = $expr;
        return $this;
    }
}
