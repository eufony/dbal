<?php

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
