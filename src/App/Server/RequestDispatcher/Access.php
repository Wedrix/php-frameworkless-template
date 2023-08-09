<?php

declare(strict_types=1);

namespace App\Server\RequestDispatcher;

interface Access
{
    public function count(): int;

    public function resetTimestamp(): int;
}

function Access(
    int $count,
    int $resetTimestamp
): Access
{
    return new class(
        count: $count,
        resetTimestamp: $resetTimestamp
    ) implements Access
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
    };
}