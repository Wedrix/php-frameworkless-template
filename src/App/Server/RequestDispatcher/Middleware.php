<?php

declare(strict_types=1);

namespace App\Server\RequestDispatcher;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

interface Middleware
{
    public function __invoke(
        ServerRequestInterface $request, 
        RequestHandlerInterface $handler
    ): ResponseInterface;
}