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

namespace Eufony\DBAL\Query\Keyword;

class Op {

    public string $type;
    public string $field;

    public static function min(string $field): static {
        return new static(__FUNCTION__, $field);
    }

    public static function max(string $field): static {
        return new static(__FUNCTION__, $field);
    }

    public static function avg(string $field): static {
        return new static(__FUNCTION__, $field);
    }

    public static function count(): static {
        return new static(__FUNCTION__, "*");
    }

    private function __construct(string $type, string $field) {
        $this->type = $type;
        $this->field = $field;
    }

}
