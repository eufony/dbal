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
