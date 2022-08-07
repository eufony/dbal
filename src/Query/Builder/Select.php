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

use Eufony\DBAL\Query\Clause\GroupByClauseTrait;
use Eufony\DBAL\Query\Clause\JoinClauseTrait;
use Eufony\DBAL\Query\Clause\LimitClauseTrait;
use Eufony\DBAL\Query\Clause\OrderByClauseTrait;
use Eufony\DBAL\Query\Clause\WhereClauseTrait;

class Select extends Query
{
    use GroupByClauseTrait;
    use JoinClauseTrait;
    use LimitClauseTrait;
    use OrderByClauseTrait;
    use WhereClauseTrait;

    protected string $table;
    protected string $alias;
    protected array $fields;

    public static function from(string $table, ?string $as = null): static
    {
        return new static($table, $as);
    }

    /**
     * {@inheritDoc}
     *
     * Use `Select::from()` to initialize an instance of this class.
     *
     * @param string $table
     * @param string|null $as
     */
    protected function __construct(string $table, string|null $as)
    {
        parent::__construct();
        $this->table = $table;

        if (isset($as)) {
            $this->alias = $as;
        }
    }

    public function fields(string ...$fields): static
    {
        $this->fields = $fields;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function affectedTables(): array
    {
        $callback = fn($join) => [$join['table'], ...$join['on']->affectedTables()];
        $having_tables = isset($this->having) ? $this->having->affectedTables() : [];
        $join_tables = isset($this->joins) ? array_map($callback, $this->joins) : [];
        $where_tables = isset($this->where) ? $this->where->affectedTables() : [];
        return [$this->table, ...$having_tables, ...$join_tables, ...$where_tables];
    }
}
