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

use Eufony\DBAL\Query\Select;

class Ex {

    public string $type;
    public array $props;

    public static function and(Ex ...$expressions): static {
        return new static(__FUNCTION__, ["ex" => $expressions]);
    }

    public static function or(Ex ...$expressions): static {
        return new static(__FUNCTION__, ["ex" => $expressions]);
    }

    public static function not(Ex $expression): static {
        return new static(__FUNCTION__, ["ex" => $expression]);
    }

    public static function lt(string $field, int|float $value): static {
        return new static(__FUNCTION__, ["field" => $field, "value" => $value]);
    }

    public static function le(string $field, int|float $value): static {
        return new static(__FUNCTION__, ["field" => $field, "value" => $value]);
    }

    public static function eq(string $field, int|float|string|bool|null $value): static {
        return new static(__FUNCTION__, ["field" => $field, "value" => $value]);
    }

    public static function ge(string $field, int|float $value): static {
        return new static(__FUNCTION__, ["field" => $field, "value" => $value]);
    }

    public static function gt(string $field, int|float $value): static {
        return new static(__FUNCTION__, ["field" => $field, "value" => $value]);
    }

    public static function ne(string $field, int|float|string|bool|null $value): static {
        return new static(__FUNCTION__, ["field" => $field, "value" => $value]);
    }

    public static function like(string $field, string $value): static {
        return new static(__FUNCTION__, ["field" => $field, "value" => $value]);
    }

    public static function in(string $field, array $value): static {
        return new static(__FUNCTION__, ["field" => $field, "value" => $value]);
    }

    public static function exists(Select $query): static {
        return new static(__FUNCTION__, ["query" => $query]);
    }

    private function __construct(string $type, array $props = []) {
        $this->type = $type;
        $this->props = $props;
    }

}
