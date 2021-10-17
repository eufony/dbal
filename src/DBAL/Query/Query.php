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

use DateInterval;
use Eufony\ORM\ORM;

/**
 * Provides abstraction away from vendor-specific query language syntax using
 * object-oriented query builders.
 * The query builder representation can than be translated by the database
 * driver in use.
 */
abstract class Query {

    public array $context = [];

    public function __clone(): void {
        unserialize(serialize($this));
    }

    /**
     * Executes this query in the given database connection.
     *
     * @param string $key
     * @param int|\DateInterval $ttl
     * @return mixed[][]
     */
    public function execute(string $key = "default", int|DateInterval $ttl = 1): array {
        return ORM::connection($key)->query($this, $ttl);
    }

}
