<?php

namespace Tests\Unit\Cache;

use Eufony\ORM\Cache\ArrayCache;
use Psr\SimpleCache\CacheInterface;

class ArrayCacheTest extends AbstractCacheTest {

    /** @inheritdoc */
    public function getCache(): CacheInterface {
        return new ArrayCache();
    }

}
