<?php
/*
 * The Eufony DBAL Package
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

namespace Eufony\DBAL\Query\Builder;

class Create extends Query
{
    protected string $type;
    protected string $name;
    protected array $fields;
    protected array $props;

    public static function table(string $name, bool $idempotent = false): static
    {
        return new static(__FUNCTION__, $name, ["idempotent" => $idempotent]);
    }

    public static function index(string $name, bool $unique = false, bool $idempotent = false): static
    {
        return new static(__FUNCTION__, $name, ["unique" => $unique, "idempotent" => $idempotent]);
    }

    /**
     * {@inheritDoc}
     *
     * Use `Create::table()` or `Create::index()` to initialize an instance of this
     * class.
     *
     * @param string $name
     * @param bool $idempotent
     */
    protected function __construct(string $type, string $name, array $props)
    {
        parent::__construct();
        $this->type = $type;
        $this->name = $name;
        $this->props = $props;
    }

    public function fields(array $fields): static
    {
        $this->fields = $fields;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function affectedTables(): array
    {
        return ($this->type === "table") ? [$this->name] : [];
    }
}
