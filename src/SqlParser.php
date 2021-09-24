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

namespace Eufony\ORM;

/**
 * Provides functionality for sanitizing data within SQL queries.
 * Protects against SQL injection attacks by properly safeguarding against
 * dangerous user data.
 */
class SqlParser {

    /**
     * Prepares an SQL query for execution by replacing placeholders within the
     * query with actual data passed in via the context array.
     * The data within the context array is first escaped using the
     * `SqlParser::quote()` method, thereby protecting against SQL injection
     * attacks.
     *
     * The query template can contain zero or more named (`:name`) or unnamed
     * (`?`) parameters.
     * Named parameters MUST have a corresponding value with a string key in
     * the context array, and MUST only contain word characters (a-zA-Z0-9_).
     * Unnamed parameters will be substituted for the next available value with
     * a non-string key.
     * The context array MUST contain the same number of unnamed values as
     * there are unnamed parameters in the template.
     * Both named and unnamed parameters can be used within the same template.
     *
     * @param string $sql
     * @param array<mixed> $context
     * @return string
     */
    public static function prepare(string $sql, array $context): string {
        // Implementation taken and modified from https://stackoverflow.com/a/66470138

        $s = chr(2); // Escape sequence for start of placeholder
        $e = chr(3); // Escape sequence for end of placeholder

        // Ensure escape sequences don't appear in any of the values
        // This shouldn't ever happen, really
        foreach ($context as $value) {
            if (str_contains($value, $s) || str_contains($value, $e)) {
                throw new InvalidArgumentException("Value within context array contains an escape sequence");
            }
        }

        $keys_named = $values_named = [];
        $keys_unnamed = $values_unnamed = [];

        // Build a regular expression for each parameter
        // Quote values to prevent SQL injections
        foreach ($context as $key => $value) {
            // Another layer of escaping backslashes (beyond the quote() method) is needed
            if (is_string($value)) $value = str_replace('\\', '\\\\', $value);

            if (is_string($key)) {
                $keys_named[] = "/$s:$key$e/";
                $values_named[$key] = SqlParser::quote($value);
            } else {
                $keys_unnamed[] = "/$s\?$e/";
                $values_unnamed[] = SqlParser::quote($value);
            }
        }

        // Ensure that exactly as many unnamed placeholders were replaced as there are unnamed parameters
        if (preg_match_all("/\?/", $sql) !== count($values_unnamed)) {
            throw new InvalidArgumentException('Mismatched number of unnamed placeholders and parameters');
        }

        // Surround placeholders with escape sequence
        // so that placeholders inside any of the values are not matched
        $sql = preg_replace(["/(\?)/", "/(:\w+)/"], "$s$1$e", $sql);

        // Replace placeholders with actual values
        // Handle named and unnamed parameters separately
        $sql = preg_replace($keys_named, $values_named, $sql);
        $sql = preg_replace($keys_unnamed, $values_unnamed, $sql, limit: 1);

        // Return result
        return $sql;
    }

    /**
     * Sanitizes PHP variables for use within an SQL query.
     * Quoted variables are theoretically safe to pass into an SQL query.
     *
     * @param mixed $value
     * @return string
     */
    public static function quote(mixed $value): string {
        // Treat each value depending on what type it is
        if (is_null($value)) return "NULL";
        if (is_int($value) || is_float($value)) return $value;
        if (is_bool($value)) return $value ? "TRUE" : "FALSE";

        /** @var string $value */

        // Escape dangerous characters in strings
        $escaped_chars = [
            "\\" => "\\\\", // single backslash to double backslash
            "'" => "''" // single quotes to double single quotes
        ];

        $value = str_replace(array_keys($escaped_chars), array_values($escaped_chars), $value);

        // Surround with single quotes
        $value = "'$value'";

        // Return result
        return $value;
    }

}
