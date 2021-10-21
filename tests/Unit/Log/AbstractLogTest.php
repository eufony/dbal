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

use PHPUnit\Framework\TestCase;
use Psr\Log\InvalidArgumentException;
use Psr\Log\LoggerInterface;

/**
 * Provides an abstract PSR-3 implementation tester.
 */
abstract class AbstractLogTest extends TestCase {

    /**
     * The PSR-3 logging implementation to test.
     *
     * @var \Psr\Log\LoggerInterface
     */
    protected LoggerInterface $logger;

    /**
     * Returns a new instance of a PSR-3 logging implementation to test.
     *
     * @return \Psr\Log\LoggerInterface
     */
    abstract public function getLogger(): LoggerInterface;

    /**
     * Returns an array of valid log levels.
     *
     * @return string[]
     */
    public function logLevels(): array {
        return ["emergency", "alert", "critical", "error", "warning", "notice", "info", "debug"];
    }

    /**
     * Data provider for invalid PSR-3 log levels.
     * Returns an invalid log level for each data set.
     *
     * @return mixed[][]
     */
    public function invalidLevels(): array {
        return [[null], [0], ["foo"]];
    }

    /**
     * Data provider for PSR-3 methods that require a log message.
     * Returns the methods name and an invalid log message for ech data set.
     *
     * @return mixed[][]
     */
    public function invalidMessages(): array {
        $methods = $this->logLevels();
        $invalid_messages = [$this->getLogger()];

        $data = [];

        foreach ($methods as $method) {
            foreach ($invalid_messages as $message) {
                $data[] = [$method, $message];
            }
        }

        return $data;
    }

    /**
     * Data provider for PSR-3 methods that accept a log context.
     * Returns the method name and an invalid context for each data set.
     *
     * @return mixed[][]
     */
    public function invalidContexts(): array {
        $methods = $this->logLevels();
        $invalid_contexts = [["exception" => "foo"]];

        $data = [];

        foreach ($methods as $method) {
            foreach ($invalid_contexts as $message) {
                $data[] = [$method, $message];
            }
        }

        return $data;
    }

    public function loggedEvents(): array {
        return [
            ["debug", "", []],
            ["info", "", []],
            ["notice", "", []],
            ["warning", "", []],
            ["error", "", []],
            ["critical", "", []],
            ["alert", "", []],
            ["emergency", "", []],
        ];
    }

    /** @inheritdoc */
    protected function setUp(): void {
        $this->logger = $this->getLogger();
    }

    /**
     * @dataProvider invalidLevels
     */
    public function testInvalidLevel(mixed $level) {
        $this->expectException(InvalidArgumentException::class);
        $this->logger->log($level, "foo");
    }

    /**
     * @dataProvider invalidMessages
     */
    public function testInvalidMessage(string $method, mixed $message) {
        $this->expectException(InvalidArgumentException::class);
        $this->logger->$method($message);
    }

    /**
     * @dataProvider invalidContexts
     */
    public function testInvalidContext(string $method, array $context) {
        $this->expectException(InvalidArgumentException::class);
        $this->logger->$method("foo", $context);
    }

}
