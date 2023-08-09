<?php

declare(strict_types=1);

namespace App\Server\RequestDispatcher;

use Comet\Request;

interface Gatekeeper
{
    public function checkIfPermitted(
        Request $request
    ): void;
}