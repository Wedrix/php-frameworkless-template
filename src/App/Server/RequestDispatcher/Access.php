<?php

declare(strict_types=1);

namespace App\Server\RequestDispatcher;

interface Access
{
    public function count(): int;

    public function resetTimestamp(): int;
}