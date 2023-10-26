<?php

declare(strict_types=1);

namespace App\Server\RequestHandler;

use App\Account;

function AccountOfUser(
    User $user
): Account
{
    $userRole = $user->role();
    $userId = $user->id();

    if (\is_null($userRole) || \is_null($userId)) {
        throw new \Exception('An anonymous user has no account.');
    }

    // TODO: Complete implementation
    return match($userRole) {
        default => throw new \Exception('Unimplemented!')
    };
}