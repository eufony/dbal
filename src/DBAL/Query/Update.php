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

namespace Eufony\ORM\DBAL\Query;

use Eufony\ORM\DBAL\Query\Clause\ValuesClauseTrait;
use Eufony\ORM\DBAL\Query\Clause\WhereClauseTrait;

/**
 * Represents an `UPDATE` SQL query.
 */
class Update extends Query {

    use ValuesClauseTrait;
    use WhereClauseTrait;

    protected string $table;

    /**
     * @param string $table
     * @return static
     */
    public static function table(string $table): static {
        return new static($table);
    }

    /**
     * Private class constructor.
     * Use `Update::table()` to initialize this class.
     *
     * @param string $table
     */
    private function __construct(string $table) {
        $this->table = $table;
    }

}
