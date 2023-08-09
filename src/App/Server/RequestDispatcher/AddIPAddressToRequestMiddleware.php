<?php

declare(strict_types=1);

namespace App\Server\RequestDispatcher;

use Comet\Request;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ResponseInterface;

use function App\IPAddressParserConfig;

function AddIPAddressToRequestMiddleware(): Middleware
{
    static $middleware;
    
    $middleware ??= new class() implements Middleware {
        public function __invoke(
            ServerRequestInterface $request, 
            RequestHandlerInterface $handler
        ): ResponseInterface
        {
            if (!$request instanceof Request) {
                throw new \Exception("Invalid request. Must be an instance of \\Comet\\Request");
            }
    
            return $handler->handle(
                $request->withAttribute(
                    attribute: $attributeName = IPAddressParserConfig()->attributeName(),
                    value: requestIPAddress(
                        request: $request,
                        attributeName: $attributeName,
                        checkProxyHeaders: IPAddressParserConfig()->checkProxyHeaders(),
                        headersToInspect: IPAddressParserConfig()->headersToInspect(),
                        trustedProxies: IPAddressParserConfig()->trustedProxies()
                    )
                )
            );
        }
    };

    return $middleware;
}