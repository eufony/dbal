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

namespace Eufony\ORM\DBAL\Query\Keyword;

use ArrayAccess;
use Eufony\ORM\BadMethodCallException;
use Eufony\ORM\OutOfBoundsException;

class Type implements ArrayAccess {

    protected string $type;
    protected array $props;

    public static function varchar(int $length): static {
        return new static(__FUNCTION__, ["length" => $length]);
    }

    public static function text(): static {
        return new static(__FUNCTION__);
    }

    public static function int(): static {
        return new static(__FUNCTION__);
    }

    public static function float(): static {
        return new static(__FUNCTION__);
    }

    public static function bool(): static {
        return new static(__FUNCTION__);
    }

    public static function null(): static {
        return new static(__FUNCTION__);
    }

    public static function blob(): static {
        return new static(__FUNCTION__);
    }

    public static function date(): static {
        return new static(__FUNCTION__);
    }

    public static function time(): static {
        return new static(__FUNCTION__);
    }

    public static function datetime(): static {
        return new static(__FUNCTION__);
    }

    private function __construct(string $type, array $props = []) {
        $this->type = $type;
        $this->props = $props;
    }

    /** @inheritdoc */
    public function offsetExists($offset): bool {
        return property_exists($this, $offset) && isset($this->$offset);
    }

    /** @inheritdoc */
    public function offsetGet($offset) {
        // Ensure property exists
        if (!$this->offsetExists($offset)) {
            throw new OutOfBoundsException("Unknown query builder property");
        }

        return $this->$offset;
    }

    /** @inheritdoc */
    public function offsetSet($offset, $value) {
        throw new BadMethodCallException("Properties is read-only");
    }

    /** @inheritdoc */
    public function offsetUnset($offset) {
        throw new BadMethodCallException("Properties is read-only");
    }

}
