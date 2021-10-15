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

namespace Eufony\DBAL\Query\Clause;

trait OrderByClauseTrait {

    public array $order;

    public function orderBy(string|array $fields): static {
        $this->order = [];

        if (is_string($fields)) {
            $fields = [$fields];
        }

        foreach ($fields as $key => $value) {
            $this->order = array_merge($this->order, is_int($key) ? [$value => "asc"] : [$key => $value]);
        }

        return $this;
    }

}
