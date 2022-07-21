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

use InvalidArgumentException;

trait OrderByClauseTrait
{
    protected array $order;

    public function orderBy(string|array $fields): static
    {
        if (is_string($fields)) {
            $fields = [$fields];
        }

        $this->order = [];

        foreach ($fields as $key => $value) {
            if (is_int($key)) {
                $key = $value;
                $value = "asc";
            }

            if (!in_array($value, ["asc", "desc"])) {
                throw new InvalidArgumentException("Unknown order modifier");
            }

            $this->order[$key] = $value;
        }

        return $this;
    }
}
