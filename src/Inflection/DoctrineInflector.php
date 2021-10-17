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

use Doctrine\Inflector\Inflector;
use Doctrine\Inflector\InflectorFactory;

/**
 * Provides an inflection implementation using the Doctrine inflector.
 */
class DoctrineInflector implements InflectorInterface {

    /**
     * The Doctrine Inflector object used internally to implement the
     * inflector interface.
     *
     * @var \Doctrine\Inflector\Inflector
     */
    private Inflector $inflector;

    /**
     * Class constructor.
     * Initializes the Doctrine Inflector with the default setup and for the
     * English language.
     */
    public function __construct() {
        $this->inflector = InflectorFactory::create()->build();
    }

    /**
     * Returns the internal Doctrine inflector object.
     *
     * @return \Doctrine\Inflector\Inflector
     */
    public function inflector(): Inflector {
        return $this->inflector;
    }

    /**
     * Returns a key-value pair of the singular to plural forms of
     * words.
     * This method can be overridden in a subclass of the inflector to
     * hard-code exceptions that the underlying inflection implementation
     * missed.
     *
     * @return string[]
     */
    public function wordExceptions(): array {
        return [];
    }

    /** @inheritdoc */
    public function tableize(string $string): string {
        return $this->inflector->tableize($string);
    }

    /** @inheritdoc */
    public function classify(string $string): string {
        return $this->inflector->classify($string);
    }

    /** @inheritdoc */
    public function camelize(string $string): string {
        return $this->inflector->camelize($string);
    }

    /** @inheritdoc */
    public function pluralize(string $string): string {
        return $this->wordExceptions()[$string] ?? $this->inflector->pluralize($string);
    }

    /** @inheritdoc */
    public function singularize(string $string): string {
        return array_search($string, $this->wordExceptions()) ?: $this->inflector->singularize($string);
    }

}
