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
use Eufony\DBAL\Query\Clause\WhereClauseTrait;

/**
 * Represents an `UPDATE` SQL query.
 */
class Update extends Query
{
    use ValuesClauseTrait;
    use WhereClauseTrait;

    protected string $table;

    /**
     * @param string $table
     * @return static
     */
    public static function table(string $table): static
    {
        return new static($table);
    }

    /**
     * {@inheritDoc}
     *
     * Use `Update::table()` to initialize an instance of this class.
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
        $where_tables = isset($this->where) ? $this->where->affectedTables() : [];
        return [$this->table, ...$where_tables];
    }
}
