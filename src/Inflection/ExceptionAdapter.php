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

namespace Eufony\ORM\Inflection;

/**
 * Provides a wrapper around an inflector interface to specify hard-coded
 * exceptions to the underlying inflection implementations rules.
 */
class ExceptionAdapter implements InflectorInterface {

    /**
     * The inflector object used internally to provide the real inflection
     * implementation.
     *
     * @var \Eufony\ORM\Inflection\InflectorInterface $inflector
     */
    private InflectorInterface $inflector;

    /**
     * Stores exceptions to the rules of converting between `PascalCase`,
     * `snake_case`, and `camelCase`.
     * Contains an array of case strings for each exception.
     *
     * @var string[][] $cases
     */
    private array $cases;

    /**
     * Stores exceptions to the pluralization and singularization rules.
     * Contains a key-value pair for each word's singular and plural form.
     *
     * @var string[] $words
     */
    private array $words;

    /**
     * Class constructor.
     * Wraps an inflection implementation to provide hard-coded exceptions to
     * its returned values.
     *
     * @param \Eufony\ORM\Inflection\InflectorInterface $inflector
     * @param string[][] $cases
     * @param string[] $words
     */
    public function __construct(InflectorInterface $inflector, array $cases = [], array $words = []) {
        $this->inflector = $inflector;
        $this->cases = $cases;
        $this->words = $words;
    }

    /**
     * Returns the internal inflection implementation.
     *
     * @return \Eufony\ORM\Inflection\InflectorInterface
     */
    public function inflector(): InflectorInterface {
        return $this->inflector;
    }

    /**
     * Returns the exceptions to the rules of converting between `PascalCase`,
     * `snake_case`, and `camelCase`.
     *
     * @return string[][]
     */
    public function cases(): array {
        return $this->cases;
    }

    /**
     * Returns the exceptions to the pluralization and singularization rules.
     *
     * @return string[]
     */
    public function words(): array {
        return $this->words;
    }

    /** @inheritdoc */
    public function tableize(string $string): string {
        $exceptions = array_flip(array_map(fn($case) => $case[0], $this->cases));
        return isset($exceptions[$string])
            ? $this->cases[$exceptions[$string]][1]
            : $this->inflector->tableize($string);
    }

    /** @inheritdoc */
    public function classify(string $string): string {
        $exceptions = array_flip(array_map(fn($case) => $case[1], $this->cases));
        return isset($exceptions[$string])
            ? $this->cases[$exceptions[$string]][0]
            : $this->inflector->classify($string);
    }

    /** @inheritdoc */
    public function camelize(string $string): string {
        $pascal_exceptions = array_flip(array_map(fn($case) => $case[0], $this->cases));
        $snake_exceptions = array_flip(array_map(fn($case) => $case[1], $this->cases));

        if (isset($pascal_exceptions[$string])) {
            return $this->cases[$pascal_exceptions[$string]][2];
        } elseif (isset($snake_exceptions[$string])) {
            return $this->cases[$snake_exceptions[$string]][2];
        } else {
            return $this->inflector->camelize($string);
        }
    }

    /** @inheritdoc */
    public function pluralize(string $string): string {
        $exceptions = $this->words;
        return $exceptions[$string] ?? $this->inflector->pluralize($string);
    }

    /** @inheritdoc */
    public function singularize(string $string): string {
        $exceptions = array_flip($this->words);
        return $exceptions[$string] ?? $this->inflector->singularize($string);
    }

}
