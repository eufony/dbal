<?php

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
