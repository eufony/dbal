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

class Drop extends Query
{
    protected array $tables;

    public static function tables(string ...$tables): static
    {
        return new static($tables);
    }

    /**
     * {@inheritDoc}
     *
     * Use `Drop::tables()` to initialize and instance of this class.
     *
     * @param array $tables
     */
    protected function __construct(array $tables)
    {
        parent::__construct();
        $this->tables = $tables;
    }
}
