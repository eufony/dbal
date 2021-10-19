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

namespace Tests\Unit\Cache;

use Eufony\ORM\Cache\ArrayCache;
use Psr\SimpleCache\CacheInterface;

/**
 * Unit tests for `\Eufony\ORM\Cache\ArrayCache`.
 */
class ArrayCacheTest extends AbstractCacheTest {

    /** @inheritdoc */
    public function getCache(): CacheInterface {
        return new ArrayCache();
    }

}
