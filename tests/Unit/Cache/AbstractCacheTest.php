<?php

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
     * @return CacheInterface
     */
    abstract public function getCache(): CacheInterface;

    /**
     * Data provider for PSR-16 methods that require a cache key parameter.
     *
     * @return array<array<mixed>>
     */
    public function invalidKeyMethods(): array {
        $methods = ["get", "set", "delete", "getMultiple", "setMultiple", "deleteMultiple", "has"];
        $invalid_keys = [null, 0, '', '{}()/\@:'];

        $data = [];

        foreach ($methods as $method) {
            // If dealing with a "multiple" method, the parameter should be an array of keys
            if (in_array([$method], $this->multipleMethods())) {
                $invalid_keys = array_map(fn($key) => [$key], $invalid_keys);
            }

            // Some PSR-16 methods require additional parameters
            $args = match ($method) {
                "set" => ["bar"],
                "setMultiple" => [["bar"]],
                default => []
            };

            foreach ($invalid_keys as $key) {
                $data[] = [$method, $key, $args];
            }
        }

        return $data;
    }

    /**
     * Data provider for PSR-16 methods that require a TTL parameter.
     *
     * @return array<array<mixed>>
     */
    public function invalidTtlMethods(): array {
        return [
            ["set", ["foo", "bar", "baz"]],
            ["setMultiple", [["foo"], ["bar"], "baz"]]
        ];
    }

    /**
     * Data provider for PSR-16 methods that operate on multiple cache items.
     *
     * @return array<array<string>>
     */
    public function multipleMethods(): array {
        return [
            ["getMultiple"],
            ["setMultiple"],
            ["deleteMultiple"]
        ];
    }

    /** @inheritdoc */
    public function setUp(): void {
        $this->cache = $this->getCache();
    }

    /**
     * @dataProvider invalidKeyMethods
     */
    public function testInvalidKeys(string $method, mixed $key, array $args) {
        $this->expectException(InvalidArgumentException::class);
        $this->cache->$method($key, ...$args);
    }

    /**
     * @dataProvider invalidTtlMethods
     */
    public function testInvalidTtl(string $method, array $args) {
        $this->expectException(InvalidArgumentException::class);
        $this->cache->$method(...$args);
    }

    /**
     * @dataProvider multipleMethods
     */
    public function testInvalidIterable(string $method) {
        $this->expectException(InvalidArgumentException::class);

        // Some PSR-16 methods require additional parameters
        $args = match ($method) {
            "setMultiple" => [["bar"]],
            default => []
        };

        $this->cache->$method("foo", ...$args);
    }

    public function testSetGet() {
        $this->cache->set('foo', 'bar');
        $this->assertEquals('bar', $this->cache->get('foo'));
    }

    public function testGetNotFound() {
        $this->assertNull($this->cache->get('not-found'));
    }

    /**
     * @depends testGetNotFound
     */
    public function testGetNotFoundDefault() {
        $default = 'chickpeas';
        $this->assertEquals($default, $this->cache->get('not-found', $default));
    }

    /**
     * @depends testSetGet
     */
    public function testSetExpireInt() {
        $this->cache->set('foo', 'bar', 1);

        // Wait 2 seconds so the cache expires
        sleep(2);

        $this->assertNull($this->cache->get('foo'));
    }

    /**
     * @depends testSetGet
     */
    public function testSetExpireDateInterval() {
        $this->cache->set('foo', 'bar', new DateInterval('PT1S'));

        // Wait 2 seconds so the cache expires
        sleep(2);

        $this->assertNull($this->cache->get('foo'));
    }

    /**
     * @depends testSetGet
     */
    public function testDelete() {
        $this->cache->set('foo', 'bar');
        $this->cache->delete('foo');
        $this->assertNull($this->cache->get('foo'));
    }

    /**
     * @depends testSetGet
     */
    public function testClearCache() {
        $this->cache->set('foo', 'bar');
        $this->cache->clear();
        $this->assertNull($this->cache->get('foo'));
    }

    public function testSetGetMultiple() {
        $keys = ["key1", "key2", "key3"];
        $values = array_combine($keys, ["value1", "value2", "value3"]);

        $this->cache->setMultiple($values);

        $result = $this->cache->getMultiple($keys);

        foreach ($result as $key => $value) {
            $this->assertTrue(isset($values[$key]));
            $this->assertEquals($values[$key], $value);
            unset($values[$key]);
        }

        // The list of values should now be empty
        $this->assertEquals([], $values);
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

        $result = $this->cache->getMultiple($generator($keys));

        foreach ($result as $key => $value) {
            $this->assertTrue(isset($values[$key]));
            $this->assertEquals($values[$key], $value);
            unset($values[$key]);
        }

        // The list of values should now be empty
        $this->assertEquals([], $values);
    }

    /**
     * @depends testSetGetMultiple
     */
    public function testSetMultipleExpireInt() {
        $values = [
            'key1' => 'value1',
            'key2' => 'value2',
            'key3' => 'value3',
        ];

        $this->cache->setMultiple($values, 1);

        // Wait 2 seconds so the cache expires
        sleep(2);

        $result = $this->cache->getMultiple(array_keys($values), 'not-found');

        $count = 0;
        $expected = [
            'key1' => 'not-found',
            'key2' => 'not-found',
            'key3' => 'not-found',
        ];

        foreach ($result as $key => $value) {
            $count++;
            $this->assertTrue(isset($expected[$key]));
            $this->assertEquals($expected[$key], $value);
            unset($expected[$key]);
        }
        $this->assertEquals(3, $count);

        // The list of values should now be empty
        $this->assertEquals([], $expected);
    }

    /**
     * @depends testSetGetMultiple
     */
    public function testSetMultipleExpireDateIntervalNotExpired() {
        $values = [
            'key1' => 'value1',
            'key2' => 'value2',
            'key3' => 'value3',
        ];

        $this->cache->setMultiple($values, new DateInterval('PT5S'));

        $result = $this->cache->getMultiple(array_keys($values));

        $count = 0;
        foreach ($result as $key => $value) {
            $count++;
            $this->assertTrue(isset($values[$key]));
            $this->assertEquals($values[$key], $value);
            unset($values[$key]);
        }
        $this->assertEquals(3, $count);

        // The list of values should now be empty
        $this->assertEquals([], $values);
    }

    /**
     * @depends testSetGetMultiple
     */
    public function testSetMultipleExpireDateIntervalExpired() {
        $values = [
            'key1' => 'value1',
            'key2' => 'value2',
            'key3' => 'value3',
        ];

        $this->cache->setMultiple($values, new DateInterval('PT1S'));

        // Wait 2 seconds so the cache expires
        sleep(2);

        $result = $this->cache->getMultiple(array_keys($values), 'not-found');

        $count = 0;
        $expected = [
            'key1' => 'not-found',
            'key2' => 'not-found',
            'key3' => 'not-found',
        ];

        foreach ($result as $key => $value) {
            $count++;
            $this->assertTrue(isset($expected[$key]));
            $this->assertEquals($expected[$key], $value);
            unset($expected[$key]);
        }
        $this->assertEquals(3, $count);

        // The list of values should now be empty
        $this->assertEquals([], $expected);
    }

    /**
     * @depends testSetGetMultiple
     */
    public function testDeleteMultiple() {
        $values = [
            'key1' => 'value1',
            'key2' => 'value2',
            'key3' => 'value3',
        ];

        $this->cache->setMultiple($values);
        $this->cache->deleteMultiple(['key1', 'key3']);

        $result = $this->cache->getMultiple(array_keys($values), 'tea');

        $expected = [
            'key1' => 'tea',
            'key2' => 'value2',
            'key3' => 'tea',
        ];

        foreach ($result as $key => $value) {
            $this->assertTrue(isset($expected[$key]));
            $this->assertEquals($expected[$key], $value);
            unset($expected[$key]);
        }

        // The list of values should now be empty
        $this->assertEquals([], $expected);
    }

    /**
     * @depends testSetGetMultiple
     */
    public function testDeleteMultipleGenerator() {
        $values = [
            'key1' => 'value1',
            'key2' => 'value2',
            'key3' => 'value3',
        ];

        $generator = function () {
            yield 'key1';
            yield 'key3';
        };

        $this->cache->setMultiple($values);
        $this->cache->deleteMultiple($generator());

        $result = $this->cache->getMultiple(array_keys($values), 'tea');

        $expected = [
            'key1' => 'tea',
            'key2' => 'value2',
            'key3' => 'tea',
        ];

        foreach ($result as $key => $value) {
            $this->assertTrue(isset($expected[$key]));
            $this->assertEquals($expected[$key], $value);
            unset($expected[$key]);
        }

        // The list of values should now be empty
        $this->assertEquals([], $expected);
    }

    /**
     * @depends testSetGet
     */
    public function testHas() {
        $this->cache->set('foo', 'bar');
        $this->assertTrue($this->cache->has('foo'));
    }

    /**
     * @depends testSetGet
     */
    public function testHasNot() {
        $this->assertFalse($this->cache->has('not-found'));
    }

    /**
     * @depends testSetGet
     */
    public function testHasExpire() {
        $this->cache->set('foo', 'bar', 1);

        // Wait 2 seconds so the cache expires
        sleep(2);

        $this->assertFalse($this->cache->has('foo'));
    }

    /**
     * @depends testSetGet
     */
    public function testHasExpireDateInterval() {
        $this->cache->set('foo', 'bar', new DateInterval('PT1S'));

        // Wait 2 seconds so the cache expires
        sleep(2);

        $this->assertFalse($this->cache->has('foo'));
    }

}
