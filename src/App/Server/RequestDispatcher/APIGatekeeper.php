<?php

declare(strict_types=1);

namespace App\Server\RequestDispatcher;

use Comet\Request;

use function App\AccessControlConfig;
use function App\Cache;
use function App\IPAddressParserConfig;

/**
 * Implements the Sliding Window algorithm
 */
function APIGatekeeper(): Gatekeeper
{
    static $apiGateKeeper;
    
    $apiGateKeeper ??= new class() implements Gatekeeper {
        public function checkIfPermitted(
            Request $request
        ): void
        {
            $time = \date_create_immutable('now');

            // Fetch user accesses
            $cacheItem = Cache()->getItem(key: 'api_access_'.$request->getAttribute(IPAddressParserConfig()->attributeName()));
    
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
                fn(WindowAccess $access) => $access->timestamp() >= ($time->getTimestamp() - AccessControlConfig()->apiAccessWindowSizeInSeconds())
            );

            // Save filtered user accesses
            Cache()->save(
                $cacheItem->set($userAccesses)
                        ->expiresAfter(AccessControlConfig()->apiAccessWindowSizeInSeconds())
            );

            // Count filtered user accesses
            $accessCount = \count($userAccesses);

            // Reject request if user accesses count > limit
            if ($accessCount > AccessControlConfig()->apiAccessLimit()) {
                throw new \Exception('Rate limit exceeded.');
            }
        }
    };

    return $apiGateKeeper;
}