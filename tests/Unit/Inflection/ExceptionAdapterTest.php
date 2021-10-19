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

namespace Tests\Unit\Inflection;

use Eufony\ORM\Inflection\DoctrineInflector;
use Eufony\ORM\Inflection\ExceptionAdapter;
use Eufony\ORM\Inflection\InflectorInterface;

/**
 * Unit tests for `\Eufony\ORM\Inflection\ExceptionAdapter`.
 */
class ExceptionAdapterTest extends AbstractInflectorTest {

    /**
     * The internal inflection implementation used to test the
     * ExceptionAdapter`.
     *
     * @var \Eufony\ORM\Inflection\InflectorInterface $internalInflector
     */
    private InflectorInterface $internalInflector;

    /**
     * Test cases for exceptions to changing between `PascalCase`,
     * `snake_case`, and `camelCase`.
     *
     * @var string[][] $cases
     */
    private array $cases;

    /**
     * Test cases for exception to pluralization and singularization.
     *
     * @var string[] $words
     */
    private array $words;

    /** @inheritdoc */
    public function getInflector(): InflectorInterface {
        $this->internalInflector = new DoctrineInflector();
        $this->cases = [["foo", "bar", "baz"]];
        $this->words = ["foo" => "bar"];
        return new ExceptionAdapter($this->internalInflector, cases: $this->cases, words: $this->words);
    }

    /** @inheritdoc */
    public function cases(): array {
        return array_merge(parent::cases(), [["foo", "bar", "baz"]]);
    }

    /** @inheritdoc */
    public function words(): array {
        return array_merge(parent::words(), [["foo", "bar"]]);
    }

    public function testInflector() {
        $this->assertSame($this->internalInflector, $this->inflector->inflector());
    }

    public function testCases() {
        $this->assertEquals($this->cases, $this->inflector->cases());
    }

    public function testWords() {
        $this->assertEquals($this->words, $this->inflector->words());
    }

}
