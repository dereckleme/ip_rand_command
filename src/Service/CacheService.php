<?php

namespace App\Service;

use Symfony\Contracts\Cache\CacheInterface;

class CacheService
{
    private CacheInterface $cache;

    public function __construct(CacheInterface $cache)
    {
        $this->cache = $cache;
    }

    public function setCacheValue(string $key, array $data): void
    {
        $cacheItem = $this->cache->getItem($key);
        $cacheItem->set($data);
        $this->cache->save($cacheItem);
    }

    public function getCacheValue(string $key)
    {
        return $this->cache->get($key, function () {
            return [];
        });
    }
}