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

use Psr\Cache\CacheItemPoolInterface;
use Psr\SimpleCache\CacheInterface;

/**
 * Provides a wrapper class to adapt a PSR-6 caching implementation to the
 * PSR-16 standards.
 */
class Psr16Adapter implements CacheInterface {

    use SimpleCacheTrait;

    /**
     * The PSR-6 cache used internally to provide the real caching
     * implementation.
     *
     * @var \Psr\Cache\CacheItemPoolInterface $cache
     */
    private CacheItemPoolInterface $cache;

    /**
     * Class constructor.
     * Wraps a PSR-6 cache to adapt it to the PSR-16 standards.
     *
     * @param \Psr\Cache\CacheItemPoolInterface $cache
     */
    public function __construct(CacheItemPoolInterface $cache) {
        $this->cache = $cache;
    }

    /**
     * Returns the internal PSR-6 cache.
     *
     * @return CacheItemPoolInterface
     */
    public function cache(): CacheItemPoolInterface {
        return $this->cache;
    }

    /** @inheritdoc */
    public function get($key, $default = null) {
        $key = $this->validateKey($key);
        $item = $this->cache->getItem($key);
        return $item->isHit() ? $item->get() : $default;
    }

    /** @inheritdoc */
    public function set($key, $value, $ttl = null): bool {
        $key = $this->validateKey($key);
        $item = $this->cache->getItem($key);
        $item->set($value)->expiresAfter($ttl);
        return $this->cache->save($item);
    }

    /** @inheritdoc */
    public function delete($key): bool {
        $key = $this->validateKey($key);
        return $this->cache->deleteItem($key);
    }

    /** @inheritdoc */
    public function clear(): bool {
        return $this->cache->clear();
    }

    /** @inheritdoc */
    public function getMultiple($keys, $default = null): array {
        $keys = $this->validateKeys($keys);

        $items = $this->cache->getItems($keys);

        $result = [];

        foreach ($items as $key => $item) {
            $result[$key] = $item->isHit() ? $item->get() : $default;
        }

        return $result;
    }

    /** @inheritdoc */
    public function setMultiple($values, $ttl = null): bool {
        $values = $this->validateIterable($values);
        $values = array_combine($this->validateKeys(array_keys($values)), (array)array_values($values));

        $items = $this->cache->getItems(array_keys($values));

        $result = true;

        foreach ($items as $key => $value) {
            $value->set($values[$key])->expiresAfter($ttl);
            $result = $this->cache->saveDeferred($value) && $result;
        }

        return $this->cache->commit() && $result;
    }

    /** @inheritdoc */
    public function deleteMultiple($keys): bool {
        $keys = $this->validateKeys($keys);
        return $this->cache->deleteItems($keys);
    }

    /** @inheritdoc */
    public function has($key): bool {
        $key = $this->validateKey($key);
        return $this->cache->hasItem($key);
    }

}
