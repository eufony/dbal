<?php
/*
 * Testsuite for the Eufony ORM Package
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

namespace Tests\Unit\Cache;

use DateInterval;
use Eufony\ORM\Cache\NullCache;
use Psr\SimpleCache\CacheInterface;

/**
 * Unit tests for `\Eufony\ORM\Cache\NullCache`.
 */
class NullCacheTest extends AbstractCacheTest {

    /** @inheritdoc */
    public function getCache(): CacheInterface {
        return new NullCache();
    }

    public function testSetGet() {
        $this->cache->set("foo", "bar");
        $this->assertNull($this->cache->get("foo"));
        $this->assertNull($this->cache->get("not-found"));
        $this->assertEquals("chickpeas", $this->cache->get("not-found", "chickpeas"));
    }

    /**
     * @depends      testSetGet
     * @dataProvider ttls
     * @group slow
     */
    public function testSetExpire(int|DateInterval $ttl, bool $expired) {
        $this->cache->set("foo", "bar", $ttl);
        $expected = null;

        // Wait 2 seconds so the cache expires
        sleep(2);

        $this->assertEquals($expected, $this->cache->get("foo"));
    }

    public function testSetGetMultiple() {
        $keys = ["key1", "key2", "key3"];
        $values = array_combine($keys, ["value1", "value2", "value3"]);

        $this->cache->setMultiple($values);
        $result = (array)$this->cache->getMultiple($keys);

        $this->assertEquals($keys, array_keys($result));

        foreach ($result as $key => $value) {
            $this->assertNull($value);
        }
    }

    public function testSetGetMultipleGenerator() {
        $keys = ["key1", "key2", "key3"];
        $values = array_combine($keys, ["value1", "value2", "value3"]);

        $generator = function ($array) {
            foreach ($array as $key => $value) {
                yield $key => $value;
            }
        };

        $this->cache->setMultiple($generator($values));
        $result = (array)$this->cache->getMultiple($generator($keys));

        $this->assertEquals($keys, array_keys($result));

        foreach ($result as $key => $value) {
            $this->assertNull($value);
        }
    }

    /**
     * @depends      testSetGetMultiple
     * @dataProvider ttls
     * @group slow
     */
    public function testSetMultipleExpire(int|DateInterval $ttl, bool $expired) {
        $keys = ["key1", "key2", "key3"];
        $values = array_combine($keys, ["value1", "value2", "value3"]);

        $this->cache->setMultiple($values, $ttl);

        // Wait 2 seconds so the cache expires
        sleep(2);

        $result = $this->cache->getMultiple($keys);

        foreach ($result as $key => $value) {
            $this->assertNull($value);
        }
    }

    /**
     * @depends testSetGetMultiple
     */
    public function testDeleteMultiple() {
        $keys = ["key1", "key2", "key3"];
        $values = array_combine($keys, ["value1", "value2", "value3"]);

        $this->cache->setMultiple($values);
        $this->cache->deleteMultiple(["key1", "key3"]);
        $result = $this->cache->getMultiple($keys, "tea");

        foreach ($result as $key => $value) {
            $this->assertEquals("tea", $value);
        }
    }

    /**
     * @depends testSetGetMultiple
     */
    public function testDeleteMultipleGenerator() {
        $keys = ["key1", "key2", "key3"];
        $values = array_combine($keys, ["value1", "value2", "value3"]);

        $generator = function () {
            yield "key1";
            yield "key3";
        };

        $this->cache->setMultiple($values);
        $this->cache->deleteMultiple($generator());
        $result = $this->cache->getMultiple(array_keys($values), "tea");

        foreach ($result as $key => $value) {
            $this->assertEquals("tea", $value);
        }
    }

    /**
     * @depends testSetGet
     */
    public function testHas() {
        $this->cache->set("foo", "bar");
        $this->assertFalse($this->cache->has("foo"));
        $this->assertFalse($this->cache->has("not-found"));
    }

    /**
     * @depends      testSetGet
     * @dataProvider ttls
     * @group slow
     */
    public function testHasExpire(int|DateInterval $ttl, bool $expired) {
        $this->cache->set("foo", "bar", $ttl);

        // Wait 2 seconds so the cache expires
        sleep(2);

        $this->assertFalse($this->cache->has("foo"));
    }

}
