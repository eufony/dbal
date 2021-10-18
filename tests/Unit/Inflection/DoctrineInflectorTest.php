<?php

namespace Tests\Unit\Inflection;

use Doctrine\Inflector\Inflector;
use Eufony\ORM\Inflection\DoctrineInflector;
use Eufony\ORM\Inflection\InflectorInterface;

/**
 * Unit tests for `\Eufony\ORM\Inflection\DoctrineInflector`.
 */
class DoctrineInflectorTest extends AbstractInflectorTest {

    /** @inheritdoc */
    public function getInflector(): InflectorInterface {
        // Wrap inflector in anonymous wrapper class to test hard-coded exceptions
        return new class() extends DoctrineInflector {

            public function wordExceptions(): array {
                return array_merge(parent::wordExceptions(), ["foo" => "bar"]);
            }

        };
    }

    /** @inheritdoc */
    public function words(): array {
        return array_merge(parent::words(), [["foo", "bar"]]);
    }

    public function testInflector() {
        $this->assertEquals(Inflector::class, get_class($this->inflector->inflector()));
    }

}
