<?php

declare(strict_types=1);

namespace App\Server\RequestDispatcher;

final class _SerializableAccess implements Access
{
    public function __construct(
        private readonly int $count,
        private readonly int $resetTimestamp
    ){}

    public function count(): int
    {
        return $this->count;
    }

    public function resetTimestamp(): int
    {
        return $this->resetTimestamp;
    }
}