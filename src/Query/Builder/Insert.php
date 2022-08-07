<?php
/*
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

namespace Eufony\DBAL\Query\Builder;

use Eufony\DBAL\Query\Clause\ValuesClauseTrait;

/**
 * Represents an `INSERT` SQL query.
 */
class Insert extends Query
{
    use ValuesClauseTrait;

    /**
     * The table to insert a row into.
     *
     * @var string $table
     */
    protected string $table;

    /**
     * Initializes a new `Insert` query builder instance.
     *
     * Requires the table name to insert into.
     *
     * @param string $table
     * @return static
     */
    public static function into(string $table): static
    {
        return new static($table);
    }

    /**
     * {@inheritDoc}
     *
     * Use `Insert::into()` to initialize an instance of this class.
     *
     * @param string $table
     */
    protected function __construct(string $table)
    {
        parent::__construct();
        $this->table = $table;
    }

    /**
     * @inheritDoc
     */
    public function affectedTables(): array
    {
        return [$this->table];
    }
}
