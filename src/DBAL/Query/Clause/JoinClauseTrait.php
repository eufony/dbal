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

namespace Eufony\ORM\DBAL\Query\Clause;

use Eufony\ORM\InvalidArgumentException;

trait JoinClauseTrait {

    protected array $joins;

    public function innerJoin(string $primary, string $foreign): static {
        $primary = explode(".", $primary);
        $foreign = explode(".", $foreign);

        if (count($primary) !== 2 || count($foreign) !== 2) {
            throw new InvalidArgumentException("Invalid primary or foreign field");
        }

        $joins ??= [];
        $this->joins[] = [
            "type" => "inner",
            "primary_table" => $primary[0],
            "primary_field" => $primary[1],
            "foreign_table" => $foreign[0],
            "foreign_field" => $foreign[1],
        ];

        return $this;
    }

}
