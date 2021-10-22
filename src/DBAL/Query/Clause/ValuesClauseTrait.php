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

namespace Eufony\ORM\DBAL\Query\Clause;

trait ValuesClauseTrait {

    protected array $values;

    public function values(array $values): static {
        foreach ($values as $key => $value) {
            $placeholder = hash("md5", uniqid(more_entropy: true));
            $this->context[$placeholder] = $value;
            $values[$key] = ":" . $placeholder;
        }

        $this->values = $values;
        return $this;
    }

}
