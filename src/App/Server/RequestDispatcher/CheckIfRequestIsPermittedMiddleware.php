<?php

declare(strict_types=1);

namespace App\Server\RequestDispatcher;

use Comet\Request;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

use function App\Server\RequestDispatcher\APIGatekeeper;

function CheckIfRequestIsPermittedMiddleware(): Middleware
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
    
            APIGatekeeper()->checkIfPermitted($request);
    
            return $handler->handle($request);
        }
    };

    return $middleware;
}