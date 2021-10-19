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
        return new DoctrineInflector();
    }

    public function testInflector() {
        $this->assertEquals(Inflector::class, get_class($this->inflector->inflector()));
    }

}
