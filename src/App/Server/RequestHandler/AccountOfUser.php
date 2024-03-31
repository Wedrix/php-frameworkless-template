<?php

declare(strict_types=1);

namespace App\Server\RequestHandler;

use App\Account;

function AccountOfUser(
    User $user
): Account
{
    \assert(userIsKnown($user), 'The user is anonymous.');

    //TODO: Complete this based on the different user roles
    return match($user->role()) {
        default => throw new \Error('Unimplemented functionality!')
    };
}