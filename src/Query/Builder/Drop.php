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

class Drop extends Query
{
    protected string $table;
    protected array $props;

    public static function table(string $table): static
    {
        return new static($table);
    }

    /**
     * {@inheritDoc}
     *
     * Use `Drop::table()` to initialize and instance of this class.
     *
     * @param string $table
     * @param mixed[] $props
     */
    protected function __construct(string $table, array $props = [])
    {
        parent::__construct();
        $this->table = $table;
        $this->props = $props;
    }

    public function idempotent(): static
    {
        $this->props['idempotent'] = true;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function affectedTables(): array
    {
        return [$this->table];
    }
}
