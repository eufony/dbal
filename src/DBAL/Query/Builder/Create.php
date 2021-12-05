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

namespace Eufony\ORM\DBAL\Query\Builder;

class Create extends Query
{
    protected string $table;
    protected array $fields;

    public static function table(string $table): static
    {
        return new static($table);
    }

    public static function tableIfNotExists(string $table): static
    {
        return new static($table);
    }

    /**
     * {@inheritDoc}
     *
     * Use `Create::table()` to initialize an instance of this class.
     *
     * @param string $table
     */
    protected function __construct(string $table)
    {
        parent::__construct();
        $this->table = $table;
    }

    public function fields(array $fields): static
    {
        $this->fields = $fields;
        return $this;
    }
}
