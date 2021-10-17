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
 * Provides a caching implementation using a PHP array, storing everything in
 * memory.
 */
class ArrayCache implements CacheInterface {

    use SimpleCacheTrait;

    /**
     * The PHP array used to store the cache items.
     *
     * @var array $cache
     */
    private array $cache;

    /**
     * Stores the timestamps of when cache items should expire.
     *
     * @var array $expirations
     */
    private array $expirations;

    /**
     * Class constructor.
     * Sets up an empty cache based on a PHP array.
     */
    public function __construct() {
        $this->clear();
    }

    /** @inheritdoc */
    public function get($key, $default = null) {
        $key = $this->validateKey($key);
        return $this->has($key) ? $this->cache[$key] : $default;
    }

    /** @inheritdoc */
    public function set($key, $value, $ttl = null): bool {
        $key = $this->validateKey($key);
        $ttl = $this->validateTtl($ttl);

        $this->cache[$key] = $value;
        if ($ttl !== null) $this->expirations[$key] = $ttl;

        return true;
    }

    /** @inheritdoc */
    public function delete($key): bool {
        $key = $this->validateKey($key);

        unset($this->cache[$key]);

        return true;
    }

    /** @inheritdoc */
    public function clear(): bool {
        $this->cache = [];
        $this->expirations = [];
        return true;
    }

    /** @inheritdoc */
    public function getMultiple($keys, $default = null): array {
        $keys = $this->validateKeys($keys);
        return array_combine($keys, array_map(fn($key) => $this->get($key, $default), $keys));
    }

    /** @inheritdoc */
    public function setMultiple($values, $ttl = null): bool {
        $values = $this->validateIterable($values);
        $values = array_combine($this->validateKeys(array_keys($values)), (array)array_values($values));

        $results = array_map(fn($k, $v) => $this->set($k, $v, $ttl), array_keys($values), array_values($values));

        return !in_array(false, $results);
    }

    /** @inheritdoc */
    public function deleteMultiple($keys): bool {
        $keys = $this->validateKeys($keys);

        $results = array_map(fn($key) => $this->delete($key), $keys);

        return !in_array(false, $results);
    }

    /** @inheritdoc */
    public function has($key): bool {
        $key = $this->validateKey($key);

        // Check if cache item exists
        if (!isset($this->cache[$key])) {
            return false;
        }

        // Check if cache item has expired
        if (isset($this->expirations[$key]) && $this->expirations[$key] < time()) {
            $this->delete($key);
            return false;
        }

        return true;
    }

}
