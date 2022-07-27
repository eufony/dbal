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

namespace Eufony\DBAL\Query\Clause;

use InvalidArgumentException;

/**
 * Provides properties and methods for query builders that support the `ORDER
 * BY` clause.
 */
trait OrderByClauseTrait
{
    /**
     * An array of key-value pairs that define the field names that the result
     * should be ordered by.
     *
     * For each field, the array key specifies the field name, and the value is one
     * of `asc` or `desc`, for ascending or descending order, respectively.
     *
     * @var string[] $order
     */
    protected array $order;

    /**
     * Sets the fields that the result should be ordered by.
     *
     * If a string parameter is passed, the result will be ordered by the given
     * field name in ascending order.
     * If instead an array parameter is passed, for each field the array may
     * contain either a string value, which is assumed to mean a field name to sort
     * by in ascending order; or a string key-value pair, where the array key is
     * the field name and the value is one of `asc` or `desc`.
     *
     * @param string|array $fields
     * @return $this
     */
    public function orderBy(string|array $fields): static
    {
        if (is_string($fields)) {
            $fields = [$fields];
        }

        $this->order = [];

        foreach ($fields as $key => $value) {
            if (is_int($key)) {
                $key = $value;
                $value = "asc";
            }

            if (!in_array($value, ["asc", "desc"])) {
                throw new InvalidArgumentException("Unknown order modifier");
            }

            $this->order[$key] = $value;
        }

        return $this;
    }
}
