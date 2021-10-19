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

namespace Eufony\DBAL\Query;

use Eufony\DBAL\Query\Clause\LimitClauseTrait;
use Eufony\DBAL\Query\Clause\OrderByClauseTrait;
use Eufony\DBAL\Query\Clause\WhereClauseTrait;

class Select extends Query {

    use LimitClauseTrait;
    use OrderByClauseTrait;
    use WhereClauseTrait;

    protected array $tables;
    protected array $fields;
    protected string $function;

    public static function from(string ...$tables): static {
        return new static($tables);
    }

    private function __construct(array $tables) {
        $this->tables = $tables;
    }

    public function fields(string ...$fields): static {
        $this->fields = $fields;
        return $this;
    }

    public function count(): int {
        $this->function = "count";
        return array_values($this->execute()[0])[0];
    }

    public function max(string $field): mixed {
        $this->fields($field);
        $this->function = "max";
        return array_values($this->execute()[0])[0];
    }

    public function min(string $field): mixed {
        $this->fields($field);
        $this->function = "min";
        return array_values($this->execute()[0])[0];
    }

}
