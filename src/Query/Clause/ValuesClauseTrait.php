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

/**
 * Provides properties and methods for query builders that support receiving a
 * key-value pair of field names and values.
 */
trait ValuesClauseTrait
{
    /**
     * An array of key-value pairs, where the array keys are field names and the
     * values the field values.
     *
     * @var mixed[] $values
     */
    protected array $values;

    /**
     * Sets the key-value pair of field names and values.
     *
     * @param array $values
     * @return $this
     */
    public function values(array $values): static
    {
        foreach ($values as $key => $value) {
            $placeholder = hash("md5", uniqid(more_entropy: true));
            $this->context[$placeholder] = $value;
            $this->values[$key] = ":$placeholder";
        }

        return $this;
    }
}
