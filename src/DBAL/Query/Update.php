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

use Eufony\DBAL\Query\Clause\ValuesClauseTrait;
use Eufony\DBAL\Query\Clause\WhereClauseTrait;

/**
 * Represents an `UPDATE` SQL query.
 */
class Update extends Query {

    use ValuesClauseTrait;
    use WhereClauseTrait;

    protected array $tables;

    /**
     * @param string ...$tables
     * @return static
     */
    public static function table(string ...$tables): static {
        return new static($tables);
    }

    /**
     * Private class constructor.
     * Use `Update::table()` to initialize this class.
     *
     * @param string[] $tables
     */
    private function __construct(array $tables) {
        $this->tables = $tables;
    }

}
