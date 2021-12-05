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

namespace Eufony\ORM\DBAL\Query\Clause;

use Eufony\ORM\DBAL\Query\Expr;

trait GroupByClauseTrait
{
    protected array $groupBy;
    protected Expr $having;

    public function groupBy(string ...$fields): static
    {
        $this->groupBy = $fields;
        return $this;
    }

    public function having(Expr $expr): static
    {
        $this->having = $expr;
        return $this;
    }
}
