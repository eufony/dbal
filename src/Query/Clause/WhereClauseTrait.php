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
 * Provides properties and methods for query builders that support the `WHERE`
 * clause.
 */
trait WhereClauseTrait
{
    /**
     * An expression to filter the rows that are affected by the query.
     *
     * @var \Eufony\DBAL\Query\Expr $where
     */
    protected Expr $where;

    /**
     * Sets the expression to filter the affected rows.
     *
     * @param \Eufony\DBAL\Query\Expr $expr
     * @return $this
     */
    public function where(Expr $expr): static
    {
        $this->where = $expr;
        $this->context = [...$this->context, ...$expr->context(recursive: true)];
        return $this;
    }
}
