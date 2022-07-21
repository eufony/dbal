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
use InvalidArgumentException;

trait JoinClauseTrait
{
    protected array $joins;

    public function innerJoin(string $table, ?string $alias = null, ?Expr $on = null): static
    {
        $this->joins ??= [];
        $this->joins[] = [
            "type" => "inner",
            "table" => $table,
            "alias" => $alias,
            "on" => $on ?? throw new InvalidArgumentException("No ON predicate given for inner join")
        ];

        return $this;
    }

    public function leftJoin(string $table, ?string $alias = null, ?Expr $on = null): static
    {
        $this->joins ??= [];
        $this->joins[] = [
            "type" => "left",
            "table" => $table,
            "alias" => $alias,
            "on" => $on ?? throw new InvalidArgumentException("No ON predicate given for left join")
        ];

        return $this;
    }
}
