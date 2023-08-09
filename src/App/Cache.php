<?php

declare(strict_types=1);

namespace App;

use Psr\Cache\CacheItemPoolInterface;

function Cache(): CacheItemPoolInterface
{
    static $cache;
    
    $cache ??= (static function (): CacheItemPoolInterface {
        $redis = new \Redis();

        $redis->connect(RedisConfig()->host(), RedisConfig()->port());
    
        return new \MatthiasMullie\Scrapbook\Psr6\Pool(
            store: new \MatthiasMullie\Scrapbook\Adapters\Redis(client: $redis)
        );
    })();

    return $cache;
}