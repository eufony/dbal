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
use PHPUnit\Framework\TestCase;
use Psr\SimpleCache\CacheInterface;
use Psr\SimpleCache\InvalidArgumentException;

/**
 * Provides an abstract PSR-16 implementation tester.
 */
abstract class AbstractCacheTest extends TestCase {

    /**
     * The PSR-16 cache implementation to test.
     *
     * @var \Psr\SimpleCache\CacheInterface $cache
     */
    protected CacheInterface $cache;

    /**
     * Returns a new instance of a PSR-16 cache implementation to test.
     *
     * @return \Psr\SimpleCache\CacheInterface
     */
    abstract public function getCache(): CacheInterface;

    /**
     * Data provider for PSR-16 methods that require a cache key parameter.
     * Returns the method name, an invalid cache key argument, and an array of
     * additional method arguments for each data set.
     *
     * @return mixed[][]
     */
    public function invalidKeys(): array {
        $methods = ["get", "set", "delete", "getMultiple", "setMultiple", "deleteMultiple", "has"];
        $invalid_keys = [null, 0, "", "{}()/\@:"];

        $data = [];

        foreach ($methods as $method) {
            // If dealing with a "multiple" method, the parameter should be an array of keys
            if (in_array($method, ["getMultiple", "setMultiple", "deleteMultiple"])) {
                $invalid_keys = array_map(fn($key) => [$key], $invalid_keys);
            }

            // Some PSR-16 methods require additional parameters
            $args = match ($method) {
                "set" => ["bar"],
                "setMultiple" => [["bar"]],
                default => []
            };

            // Push arguments to data set
            foreach ($invalid_keys as $key) {
                $data[] = [$method, $key, $args];
            }
        }

        // Return result
        return $data;
    }

    /**
     * Data provider for PSR-16 methods that require a TTL parameter.
     * Returns the method name and an array of method arguments for each data
     * set.
     *
     * @return mixed[][]
     */
    public function ttlMethods(): array {
        return [
            ["set", ["foo", "bar"]],
            ["setMultiple", [["foo"], ["bar"]]],
        ];
    }

    /**
     * Data provider for PSR-16 methods that change their behaviour when cache
     * items expire.
     * Returns a valid TTL parameter and whether the TTL expires after 2
     * seconds.
     *
     * @return mixed[][]
     */
    public function ttls(): array {
        return [
            [1, true],
            [5, false],
            [new DateInterval("PT1S"), true],
            [new DateInterval("PT5S"), false],
        ];
    }

    /**
     * Data provider for PSR-16 methods that operate on multiple cache items.
     * Returns the method name and an array of method arguments for each data
     * set.
     *
     * @return string[][]
     */
    public function multipleMethods(): array {
        return [
            ["getMultiple", [["foo"]]],
            ["setMultiple", [["foo"], ["bar"]]],
            ["deleteMultiple", [["foo"]]],
        ];
    }

    /** @inheritdoc */
    protected function setUp(): void {
        $this->cache = $this->getCache();
    }

    /**
     * @dataProvider invalidKeys
     */
    public function testInvalidKeys(string $method, mixed $key, array $args) {
        $this->expectException(InvalidArgumentException::class);
        $this->cache->$method($key, ...$args);
    }

    /**
     * @dataProvider ttlMethods
     */
    public function testInvalidTtl(string $method, array $args) {
        $args[] = "ttl";
        $this->expectException(InvalidArgumentException::class);
        $this->cache->$method(...$args);
    }

    /**
     * @dataProvider multipleMethods
     */
    public function testInvalidIterable(string $method, array $args) {
        $args[0] = "foo";
        $this->expectException(InvalidArgumentException::class);
        $this->cache->$method(...$args);
    }

    public function testSetGet() {
        $this->cache->set("foo", "bar");
        $this->assertEquals("bar", $this->cache->get("foo"));
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
        $expected = $expired ? null : "bar";

        // Wait 2 seconds so the cache expires
        sleep(2);

        $this->assertEquals($expected, $this->cache->get("foo"));
    }

    /**
     * @depends testSetGet
     */
    public function testDelete() {
        $this->cache->set("foo", "bar");
        $this->cache->delete("foo");
        $this->assertNull($this->cache->get("foo"));
    }

    /**
     * @depends testSetGet
     */
    public function testClearCache() {
        $this->cache->set("foo", "bar");
        $this->cache->clear();
        $this->assertNull($this->cache->get("foo"));
    }

    public function testSetGetMultiple() {
        $keys = ["key1", "key2", "key3"];
        $values = array_combine($keys, ["value1", "value2", "value3"]);

        $this->cache->setMultiple($values);
        $result = (array)$this->cache->getMultiple($keys);

        $this->assertEquals($keys, array_keys($result));

        foreach ($result as $key => $value) {
            $this->assertEquals($values[$key], $value);
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
            $this->assertEquals($values[$key], $value);
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
        $expected = $expired ? array_fill_keys($keys, null) : $values;

        foreach ($result as $key => $value) {
            $this->assertEquals($expected[$key], $value);
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

        $expected = [
            "key1" => "tea",
            "key2" => "value2",
            "key3" => "tea",
        ];

        foreach ($result as $key => $value) {
            $this->assertEquals($expected[$key], $value);
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

        $expected = [
            "key1" => "tea",
            "key2" => "value2",
            "key3" => "tea",
        ];

        foreach ($result as $key => $value) {
            $this->assertEquals($expected[$key], $value);
        }
    }

    /**
     * @depends testSetGet
     */
    public function testHas() {
        $this->cache->set("foo", "bar");
        $this->assertTrue($this->cache->has("foo"));
        $this->assertFalse($this->cache->has("not-found"));
    }

    /**
     * @depends      testSetGet
     * @dataProvider ttls
     * @group slow
     */
    public function testHasExpire(int|DateInterval $ttl, bool $expired) {
        $this->cache->set("foo", "bar", $ttl);
        $expected = !$expired;

        // Wait 2 seconds so the cache expires
        sleep(2);

        $this->assertEquals($expected, $this->cache->has("foo"));
    }

}
