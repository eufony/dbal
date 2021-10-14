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

namespace Eufony\ORM\Cache;

use Psr\SimpleCache\CacheInterface;

/**
 * Provides a "black-hole" caching implementation.
 * Performs the same validation on the method parameters without actually
 * caching anything.
 */
class NullCache implements CacheInterface {

    use SimpleCacheTrait;

    /**
     * Class constructor.
     */
    public function __construct() {
    }

    /** @inheritdoc */
    public function get($key, $default = null): mixed {
        $this->validateKey($key);
        return $default;
    }

    /** @inheritdoc */
    public function set($key, $value, $ttl = null): bool {
        $this->validateKey($key);
        return false;
    }

    /** @inheritdoc */
    public function delete($key): bool {
        $this->validateKey($key);
        return false;
    }

    /** @inheritdoc */
    public function clear(): bool {
        return false;
    }

    /** @inheritdoc */
    public function getMultiple($keys, $default = null): array {
        $keys = $this->validateIterable($keys);

        foreach ($keys as $value) {
            $this->validateKey($value);
        }

        return array_fill_keys($keys, $default);
    }

    /** @inheritdoc */
    public function setMultiple($values, $ttl = null): bool {
        $values = $this->validateIterable($values);

        foreach (array_keys($values) as $key) {
            $this->validateKey($key);
        }

        return false;
    }

    /** @inheritdoc */
    public function deleteMultiple($keys): bool {
        $keys = $this->validateIterable($keys);

        foreach ($keys as $value) {
            $this->validateKey($value);
        }

        return false;
    }

    /** @inheritdoc */
    public function has($key): bool {
        $this->validateKey($key);
        return false;
    }

}
