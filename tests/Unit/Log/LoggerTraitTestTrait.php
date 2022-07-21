<?php
/*
 * The Eufony DBAL Package
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

namespace Eufony\DBAL\Tests\Unit\Log;

use Eufony\DBAL\Log\LoggerTrait;
use Psr\Log\InvalidArgumentException;
use Psr\Log\LogLevel;
use ReflectionClass;

/**
 * Unit tests for PSR-3 loggers that inherit from
 * `\Eufony\DBAL\Log\LoggerTrait`.
 */
trait LoggerTraitTestTrait
{
    /**
     * Forcibly invokes a private method in LoggerTrait and returns the result.
     *
     * @param string $method
     * @param mixed ...$args
     * @return mixed
     */
    protected function invokeTraitMethod(string $method, mixed ...$args): mixed
    {
        $method = (new ReflectionClass($this->logger))->getMethod($method);
        $method->setAccessible(true);

        $result = $method->invoke($this->logger, ...$args);

        $method->setAccessible(false);
        return $result;
    }

    /**
     * Data provider for PSR-3 messages and contexts.
     * Returns the log message, context, and expected fully interpolated message
     * string for each data set.
     *
     * @return mixed[][]
     */
    public function interpolatedMessages(): array
    {
        $logged_events = $this->loggedEvents();
        $interpolated_strings = [
            "Hello, world!",
            "Hello, bar",
            "bar bar",
            "{bar}",
            "value1 value2",
            "value2 value1",
            (string)$logged_events[6][2]["foo"],
            "",
        ];

        $data = [];

        // Push arguments to data set
        foreach ($logged_events as $index => $event) {
            $data[] = [$event[1], $event[2], $interpolated_strings[$index]];
        }

        // Return result
        return $data;
    }

    public function testLoggerHasLoggerTrait()
    {
        $this->assertTrue(in_array(LoggerTrait::class, class_uses(get_class($this->logger))));
    }

    /**
     * @depends      testLoggerHasLoggerTrait
     * @dataProvider invalidLevels
     */
    public function testMinLevelInvalidLevel(mixed $level)
    {
        $this->expectException(InvalidArgumentException::class);
        $this->logger->minLevel($level ?? 0);
    }

    /**
     * @depends      testLoggerHasLoggerTrait
     * @dataProvider invalidLevels
     */
    public function testMaxLevelInvalidLevel(mixed $level)
    {
        $this->expectException(InvalidArgumentException::class);
        $this->logger->maxLevel($level ?? 0);
    }

    /**
     * @depends testLoggerHasLoggerTrait
     */
    public function testMinLevel()
    {
        $this->assertEquals(LogLevel::DEBUG, $this->logger->minLevel());
        $this->logger->minLevel(LogLevel::INFO);
        $this->assertEquals(LogLevel::INFO, $this->logger->minLevel());
    }

    /**
     * @depends testLoggerHasLoggerTrait
     */
    public function testMaxLevel()
    {
        $this->assertEquals(LogLevel::EMERGENCY, $this->logger->maxLevel());
        $this->logger->maxLevel(LogLevel::ALERT);
        $this->assertEquals(LogLevel::ALERT, $this->logger->maxLevel());
    }

    /**
     * @depends testLoggerHasLoggerTrait
     */
    public function testCompareLevels()
    {
        $this->assertTrue($this->invokeTraitMethod("psr3_compareLevels", "debug", "debug", "emergency"));
        $this->assertTrue($this->invokeTraitMethod("psr3_compareLevels", "debug", "debug", "info"));
        $this->assertFalse($this->invokeTraitMethod("psr3_compareLevels", "debug", "info", "emergency"));
        $this->assertTrue($this->invokeTraitMethod("psr3_compareLevels", "emergency", "emergency", "emergency"));
    }

    /**
     * @depends      testLoggerHasLoggerTrait
     * @dataProvider interpolatedMessages
     */
    public function testInterpolate(string $message, array $context, string $expected)
    {
        $this->assertEquals($expected, $this->invokeTraitMethod("psr3_interpolateMessage", $message, $context));
    }

    /**
     * @depends testLoggerHasLoggerTrait
     */
    public function testInterpolateInvalid()
    {
        $message = "{key1}";
        $context = ["key1" => $this->logger];

        $this->expectException(InvalidArgumentException::class);
        $this->invokeTraitMethod("psr3_interpolateMessage", $message, $context);
    }
}
