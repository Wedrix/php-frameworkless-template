<?php

declare(strict_types=1);

namespace App\Server\RequestHandler;

interface WindowAccess
{
    public function timestamp(): int;
}

function WindowAccess(
    int $timestamp
): WindowAccess
{
    /**
     * Needed since PHP cannot serialize Anonymous Classes (as of v8.2).
     */
    return new _SerializableWindowAccess(
        timestamp: $timestamp
    );
}