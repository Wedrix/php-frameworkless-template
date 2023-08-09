<?php

declare(strict_types=1);

namespace App\Server\RequestDispatcher;

use Psr\Http\Message\ResponseInterface as MessageResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

use function App\DataMapper;

function CleanUpAfterRequestMiddleware(): Middleware
{
    static $middleware;
    
    $middleware ??= new class() implements Middleware {
        public function __invoke(
            ServerRequestInterface $request, 
            RequestHandlerInterface $handler
        ): MessageResponseInterface
        {
            $response = $handler->handle($request);

            DataMapper()->clear();

            return $response;
        }
    };

    return $middleware;
}