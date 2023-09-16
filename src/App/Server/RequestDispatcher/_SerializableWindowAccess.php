<?php

declare(strict_types=1);

namespace App\Server\RequestDispatcher;

/**
 * Needed since PHP cannot serialize Anonymous Classes (as of v8.2).
 */
final class _SerializableWindowAccess implements WindowAccess
{
    public function __construct(
        private readonly int $timestamp
    ){}

    public function timestamp(): int
    {
        return $this->timestamp;
    }
}