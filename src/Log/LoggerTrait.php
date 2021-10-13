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

namespace Eufony\ORM\Log;

use Exception;
use Psr\Log\InvalidArgumentException;
use Psr\Log\LogLevel;
use ReflectionClass;
use Stringable;

/**
 * Provides common functionality for implementing the PSR-3 logging standards.
 */
trait LoggerTrait {

    /**
     * The minimum level to filter for when logging.
     * Defaults to the lowest level.
     *
     * @var string $minLevel
     */
    private string $minLevel = LogLevel::DEBUG;

    /**
     * The maximum level to filter for when logging.
     * Defaults to the highest level.
     *
     * @var string $maxLevel
     */
    private string $maxLevel = LogLevel::EMERGENCY;

    /**
     * Returns the current minimum log level.
     * If `$level` is set, sets the new minimum and returns the previous value.
     *
     * **Notice:** This method is not a part of the PSR-3 standard.
     *
     * @param string|null $level
     * @return string
     */
    public function minLevel($level = null): string {
        $prev = $this->minLevel;
        [$this->minLevel] = $this->validateLevels($level ?? $this->minLevel);
        return $prev;
    }

    /**
     * Returns the current maximum log level.
     * If `$level` is set, sets the new maximum and returns the previous value.
     *
     * **Notice:** This method is not a part of the PSR-3 standard.
     *
     * @param string|null $level
     * @return string
     */
    public function maxLevel($level = null): string {
        $prev = $this->maxLevel;
        [$this->maxLevel] = $this->validateLevels($level ?? $this->maxLevel);
        return $prev;
    }

    /**
     * Checks if a log level falls between a set minimum and maximum.
     * Returns true if the log level is within the set range, false otherwise.
     *
     * Example usage:
     * ```
     * if (!$this->compareLevels($level, $this->minLevel, $this->maxLevel)) return;
     * ```
     *
     * @param string $level
     * @param string $minLevel Defaults to the lowest level
     * @param string $maxLevel Defaults to the highest level
     * @return bool
     */
    private function compareLevels($level, $minLevel = LogLevel::DEBUG, $maxLevel = LogLevel::EMERGENCY): bool {
        // Validate log levels
        [$minLevel, $level, $maxLevel] = $this->validateLevels($minLevel, $level, $maxLevel);

        // Compare index of constants defined in the LogLevel class
        // Lower index means higher priority
        $levels = array_values((new ReflectionClass(LogLevel::class))->getConstants());
        $i1 = array_search($minLevel, $levels);
        $i2 = array_search($level, $levels);
        $i3 = array_search($maxLevel, $levels);

        return $i1 >= $i2 && $i2 > $i3;
    }

    /**
     * Validates the log levels passed to various logger methods.
     * Returns an array of the validated log levels for easy processing.
     *
     * Example usage:
     * ```
     * [$l1, $l2] = $this->validateLevels($l1, $l2);
     * ```
     *
     * @param array<string> $levels
     * @return array<string>
     */
    private function validateLevels(...$levels): array {
        $validated = [];

        foreach ($levels as $level) {
            // Ensure log level can be typecast to string
            if (!is_scalar($level) && !($level instanceof Stringable)) {
                throw new InvalidArgumentException("Log level must be able to be typecast to a string");
            }

            // Ensure valid log level is passed
            // Grab valid log levels from constants defined in the LogLevel class
            $levels = (new ReflectionClass(LogLevel::class))->getConstants();

            if (!in_array($level, $levels)) {
                throw new InvalidArgumentException("Invalid log level '$level'");
            }

            // Ensure objects are cast to strings
            /** @var string $level */
            $level = "$level";

            // Push result to array
            $validated[] = $level;
        }

        // Return result
        return $validated;
    }

    /**
     * Validates parameters passed to the `LoggerInterface::log()` method.
     * Returns an array of the validated parameters for easy processing.
     *
     * Example usage:
     * ```
     * [$level, $message, $context] = $this->validateParams($level, $message, $context);
     * ```
     *
     * @param string $level
     * @param string|Stringable $message
     * @param array<mixed> $context
     * @return array
     */
    private function validateParams($level, $message, array $context = []): array {
        // Ensure log message can be typecast to string
        if ($message !== null && !is_scalar($message) && !($message instanceof Stringable)) {
            throw new InvalidArgumentException("Log message must be able to be typecast to a string");
        }

        // If "exception" key exists, ensure it is an instance of Exception
        if (array_key_exists("exception", $context) && !($context['exception'] instanceof Exception)) {
            throw new InvalidArgumentException("'exception' key in context array must be an instance of \Exception");
        }

        // Ensure objects are cast to strings
        /** @var string $message */
        $message = "$message";

        // Validate log level
        [$level] = $this->validateLevels($level);

        // Return result
        return [$level, $message, $context];
    }

    /**
     * Interpolates context values into the message placeholders.
     * Returns the interpolated message for easy processing.
     *
     * Example usage:
     * ```
     * $message = $this->interpolate($message, $context);
     * ```
     *
     * @param string $message
     * @param array $context
     * @return string
     */
    private function interpolate(string $message, array $context = []): string {
        // Build a replacement array with braces around the context keys
        $replace = [];

        foreach ($context as $key => $val) {
            // Ensure log value can be typecast to string
            if ($val !== null && !is_scalar($val) && !($val instanceof Stringable)) {
                throw new InvalidArgumentException("Value in context array must be able to be typecast to a string");
            }

            // Ensure objects are cast to strings
            /** @var string $val */
            $val = "$val";

            // Add key-value pair to replacement array
            $replace['{' . $key . '}'] = $val;
        }

        // Interpolate replacement values into the message
        $message = strtr($message, $replace);

        // Return result
        return $message;
    }

}
