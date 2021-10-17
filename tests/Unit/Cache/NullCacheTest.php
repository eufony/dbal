<?php

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
        $this->cache->set('foo', 'bar');
        $this->assertNull($this->cache->get('foo'));
    }

    public function testSetGetMultiple() {
        $keys = ["key1", "key2", "key3"];
        $values = array_combine($keys, ["value1", "value2", "value3"]);

        $this->cache->setMultiple($values);

        $result = $this->cache->getMultiple($keys);

        foreach ($result as $key => $value) {
            $this->assertTrue(isset($values[$key]));
            $this->assertNull($value);
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
            $this->assertNull($value);
            unset($values[$key]);
        }

        // The list of values should now be empty
        $this->assertEquals([], $values);
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
            $this->assertNull($value);
            unset($values[$key]);
        }
        $this->assertEquals(3, $count);

        // The list of values should now be empty
        $this->assertEquals([], $values);
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
            'key2' => 'tea',
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
            'key2' => 'tea',
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
        $this->assertFalse($this->cache->has('foo'));
    }

}
