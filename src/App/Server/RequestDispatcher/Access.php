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
    /**
     * Needed since PHP cannot serialize Anonymous Classes (as of v8.2).
     */
    return new _SerializableAccess(
        count: $count,
        resetTimestamp: $resetTimestamp
    );
}