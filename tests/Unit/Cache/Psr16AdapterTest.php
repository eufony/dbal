<?php

namespace Tests\Unit\Cache;

use Cache\Adapter\PHPArray\ArrayCachePool;
use Eufony\ORM\Cache\Psr16Adapter;
use Psr\Cache\CacheItemPoolInterface;
use Psr\SimpleCache\CacheInterface;

class Psr16AdapterTest extends AbstractCacheTest {

    /**
     * The internal PSR-6 cache implementation used to test the PSR-16 adapter.
     *
     * @var \Psr\Cache\CacheItemPoolInterface $internalCache
     */
    private CacheItemPoolInterface $internalCache;

    /** @inheritdoc */
    public function getCache(): CacheInterface {
        $this->internalCache = new ArrayCachePool();
        return new Psr16Adapter($this->internalCache);
    }

    public function testGetInternalCachePool() {
        $this->assertSame($this->internalCache, $this->cache->cache());
    }

}
