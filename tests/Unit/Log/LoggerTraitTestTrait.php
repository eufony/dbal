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

namespace Tests\Unit\Log;

use Eufony\ORM\Log\LoggerTrait;
use Psr\Log\InvalidArgumentException;
use Psr\Log\LogLevel;
use ReflectionClass;

/**
 * Unit tests for `\Eufony\ORM\Log\LoggerTrait`.
 */
trait LoggerTraitTestTrait {

    /**
     * Forcibly invokes a private method in LoggerTrait and returns the result.
     *
     * @param string $method
     * @param mixed ...$args
     * @return mixed
     */
    protected function invokeTraitMethod(string $method, mixed ...$args): mixed {
        $method = (new ReflectionClass($this->logger))->getMethod($method);
        $method->setAccessible(true);

        $result = $method->invoke($this->logger, ...$args);

        $method->setAccessible(false);
        return $result;
    }

    public function testLoggerHasLoggerTrait() {
        $this->assertTrue(in_array(LoggerTrait::class, class_uses(get_class($this->logger))));
    }

    /**
     * @depends      testLoggerHasLoggerTrait
     * @dataProvider invalidLevels
     */
    public function testMinLevelInvalidLevel(mixed $level) {
        $this->expectException(InvalidArgumentException::class);
        $this->logger->minLevel($level ?? 0);
    }

    /**
     * @depends      testLoggerHasLoggerTrait
     * @dataProvider invalidLevels
     */
    public function testMaxLevelInvalidLevel(mixed $level) {
        $this->expectException(InvalidArgumentException::class);
        $this->logger->maxLevel($level ?? 0);
    }

    /**
     * @depends testLoggerHasLoggerTrait
     */
    public function testMinLevel() {
        $this->assertEquals(LogLevel::DEBUG, $this->logger->minLevel());
        $this->logger->minLevel(LogLevel::INFO);
        $this->assertEquals(LogLevel::INFO, $this->logger->minLevel());
    }

    /**
     * @depends testLoggerHasLoggerTrait
     */
    public function testMaxLevel() {
        $this->assertEquals(LogLevel::EMERGENCY, $this->logger->maxLevel());
        $this->logger->maxLevel(LogLevel::ALERT);
        $this->assertEquals(LogLevel::ALERT, $this->logger->maxLevel());
    }

    /**
     * @depends testLoggerHasLoggerTrait
     */
    public function testCompareLevels() {
        $this->assertTrue($this->invokeTraitMethod("compareLevels", "debug", "debug", "emergency"));
        $this->assertTrue($this->invokeTraitMethod("compareLevels", "debug", "debug", "info"));
        $this->assertFalse($this->invokeTraitMethod("compareLevels", "debug", "info", "emergency"));
        $this->assertTrue($this->invokeTraitMethod("compareLevels", "emergency", "emergency", "emergency"));
    }

    /**
     * @depends testLoggerHasLoggerTrait
     */
    public function testInterpolate() {
        $message = "{key1} {key2} {key3} {key4} {key5}";
        $context = ["key1" => "value1", "key2" => 2, "key3" => null, "key4" => true];
        $expected = "value1 2  1 {key5}";

        $this->assertEquals($expected, $this->invokeTraitMethod("interpolate", $message, $context));
    }

    /**
     * @depends testLoggerHasLoggerTrait
     */
    public function testInterpolateNested() {
        $message = "{key1} {key2}";
        $context = ["key1" => "{value1}", "key2" => "{{value2}}"];
        $expected = "{value1} {{value2}}";

        $this->assertEquals($expected, $this->invokeTraitMethod("interpolate", $message, $context));
    }

    /**
     * @depends testLoggerHasLoggerTrait
     */
    public function testInterpolateInvalid() {
        $message = "{key1}";
        $context = ["key1" => $this->logger];

        $this->expectException(InvalidArgumentException::class);
        $this->invokeTraitMethod("interpolate", $message, $context);
    }

}