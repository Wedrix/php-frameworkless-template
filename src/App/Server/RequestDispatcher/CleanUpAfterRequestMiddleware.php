<?php

declare(strict_types=1);

namespace App\Server\RequestDispatcher;

use Comet\Request;
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
            if (!$request instanceof Request) {
                throw new \Exception('Invalid request. Must be an instance of \\Comet\\Request.');
            }

            $response = $handler->handle($request);

            DataMapper()->clear();

            return $response;
        }
    };

    return $middleware;
}