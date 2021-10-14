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

use Eufony\ORM\InvalidArgumentException;
use Stringable;
use Traversable;

/**
 * Provides common functionality for implementing the PSR-16 caching standards.
 */
trait SimpleCacheTrait {

    /**
     * Validates the cache key passed to the various cache methods.
     * Returns the validated key for easy processing
     *
     * Example usage:
     * ```
     * $key = $this->validateKey($key);
     * ```
     *
     * @param string|Stringable $key
     * @return string
     */
    private function validateKey($key): string {
        // Ensure key can be typecast to string
        if (!is_string($key) && !($key instanceof Stringable)) {
            throw new InvalidArgumentException("Cache key must be a string");
        }

        // Ensure key is not an empty string
        if ($key === '') {
            throw new InvalidArgumentException('Cache key must not be empty');
        }

        // Ensure key does not contain reserved characters
        if (strpbrk($key, '{}()/\@:') !== false) {
            throw new InvalidArgumentException("Cache key contains reserved characters: {}()/\@:");
        }

        // Ensure objects are cast to strings
        /** @var string $key */
        $key = "$key";

        // Return result
        return $key;
    }

    /**
     * Validates the iterable parameters passed to the various cache methods.
     * Returns an array from the iterable for easy processing.
     *
     * Example usage:
     * ```
     * $keys = $this->validateIterable($keys);
     * ```
     *
     * @param iterable $iterable
     * @return array
     */
    private function validateIterable($iterable): array {
        // No processing for arrays required
        if (is_array($iterable)) {
            return $iterable;
        }

        // Cast iterables to array
        if ($iterable instanceof Traversable) {
            return iterator_to_array($iterable);
        }

        // Ensure an iterable is passed
        throw new InvalidArgumentException('Invalid iterable parameter');
    }

}