<?php

declare(strict_types=1);

namespace App\Server\RequestDispatcher;

use Comet\Request;
use Comet\Response;

interface Controller
{
    /**
     * @param array<string,mixed> $args
     */
    public function __invoke(
        Request $request,
        Response $response,
        array $args = []
    ): Response;
}