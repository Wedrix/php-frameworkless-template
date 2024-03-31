<?php

declare(strict_types=1);

namespace App\Server\RequestHandler;

use function App\Cache;
use function App\Config;

interface APIRateLimiter
{
    public function checkThatClientIsAllowed(
        IPAddress $clientIPAddress
    ): void;
}

/**
 * Uses the Sliding Window algorithm
 */
function APIRateLimiter(): APIRateLimiter
{
    static $apiRateLimiter;

    $apiRateLimiter ??= new class() implements APIRateLimiter {
        public function checkThatClientIsAllowed(
            IPAddress $clientIPAddress
        ): void
        {
            $time = \date_create_immutable('now');
    
            // Fetch user accesses
            $cacheItem = Cache()->getItem(key: "ip_address_{$clientIPAddress}_api_access");
    
            $userAccesses = $cacheItem->isHit() 
                                    ? $cacheItem->get() 
                                    : [];
    
            // Add current timestamp as new user access
            $userAccesses[] = WindowAccess(
                timestamp: $time->getTimestamp()
            );
    
            // Filter user accesses where timestamps < current timestamp - access window size in seconds
            $userAccesses = \array_filter(
                $userAccesses,
                static fn(WindowAccess $access) => $access->timestamp() >= ($time->getTimestamp() - Config()->accessControlApiAccessWindowSizeInSeconds())
            );
    
            // Save filtered user accesses
            Cache()->save(
                $cacheItem->set($userAccesses)
                        ->expiresAfter(Config()->accessControlApiAccessWindowSizeInSeconds())
            );
    
            // Count filtered user accesses
            $accessCount = \count($userAccesses);
    
            // Exceeded if user accesses count > limit
            if ($accessCount > Config()->accessControlApiAccessLimit()) {
                throw new \ConstraintViolationException('Rate limit exceeded! Try again after a few minutes.');
            }
        }
    };

    return $apiRateLimiter;
}