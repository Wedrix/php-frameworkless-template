<?php

declare(strict_types=1);

namespace App\Server\RequestDispatcher;

use Comet\Request;
use Comet\Response;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ResponseInterface;

use function App\AccessControlConfig;

function HandleOptionsRequestAndAddCORSHeadersMIddleware(): Middleware
{
    static $middleware;
    
    $middleware ??= new class() implements Middleware {
        public function __invoke(
            ServerRequestInterface $request, 
            RequestHandlerInterface $handler
        ): ResponseInterface
        {
            if (!$request instanceof Request) {
                throw new \Exception('Invalid request. Must be an instance of \\Comet\\Request.');
            }

            $response = ($request->getMethod() === 'OPTIONS') 
                                ? new Response()
                                : $handler->handle($request);
    
            $origin = requestOrigin($request);
    
            $allowedOrigins = AccessControlConfig()->allowedOrigins();
    
            $response->withHeader('Access-Control-Allow-Origin', \in_array($origin, $allowedOrigins) ? $origin : $allowedOrigins[0])
                    ->withHeader('Vary', 'Origin')
                    ->withHeader('Access-Control-Allow-Headers', \implode(',', AccessControlConfig()->allowedHeaders()))
                    ->withHeader('Access-Control-Allow-Methods', \implode(',', AccessControlConfig()->allowedMethods()))
                    ->withHeader('Access-Control-Expose-Headers', AccessControlConfig()->exposeHeaders());
            
            if (AccessControlConfig()->allowCredentials()) {
                $response->withHeader('Access-Control-Allow-Credentials', 'true');
            }
    
            return $response;   
        }
    };

    return $middleware;
}