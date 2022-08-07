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
    protected string $type;
    protected string $name;

    public static function table(string $name): static
    {
        return new static(__FUNCTION__, $name);
    }

    public static function index(string $name): static
    {
        return new static(__FUNCTION__, $name);
    }

    /**
     * {@inheritDoc}
     *
     * Use `Drop::table()` to initialize and instance of this class.
     *
     * @param string $type
     * @param string $name
     */
    protected function __construct(string $type, string $name)
    {
        parent::__construct();
        $this->type = $type;
        $this->name = $name;
    }

    /**
     * @inheritDoc
     */
    public function affectedTables(): array
    {
        return ($this->type === "table") ? [$this->name] : [];
    }
}
