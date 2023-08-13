<?php

declare(strict_types=1);

namespace App\Server\RequestDispatcher;

use Comet\Request;

use function App\AccessControlConfig;
use function App\Cache;
use function App\IPAddressParserConfig;

function APIGatekeeper(): Gatekeeper
{
    static $apiGateKeeper;
    
    $apiGateKeeper ??= new class() implements Gatekeeper {
        public function checkIfPermitted(
            Request $request
        ): void
        {
            $time = \date_create_immutable('now');
    
            $cacheItem = Cache()->getItem(key: 'api_gatekeeper'.$request->getAttribute(IPAddressParserConfig()->attributeName()));
    
            $previousApiAccess = $cacheItem->isHit() 
                                    ? $cacheItem->get() 
                                    : new _SerializableAccess(
                                        count: 0,
                                        resetTimestamp: $time->modify(
                                            '+'.AccessControlConfig()->apiAccessWindow().' seconds'
                                        )->getTimestamp()
                                    );
    
            Cache()->save(
                $cacheItem->set(
                            $currentApiAccess = new _SerializableAccess(
                                count: $previousApiAccess->count() + 1,
                                resetTimestamp: $previousApiAccess->resetTimestamp()
                            )
                        )
                        ->expiresAfter(
                            ($currentApiAccess->count() > AccessControlConfig()->apiAccessLimit()) 
                                ? intval(
                                    min(
                                        AccessControlConfig()->apiAccessWindowGrowthFactor() 
                                            ** ($currentApiAccess->count() - AccessControlConfig()->apiAccessLimit()),
                                        AccessControlConfig()->apiAccessWindowMaxSize()
                                    )
                                )
                                : $previousApiAccess->resetTimestamp() - $time->getTimestamp()
                        )
            );
    
            if ($currentApiAccess->count() > AccessControlConfig()->apiAccessLimit()) {
                throw new \Exception('Rate limit exceeded.');
            }
        }
    };

    return $apiGateKeeper;
}