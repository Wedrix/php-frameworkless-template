<?php

declare(strict_types=1);

namespace App\Server\RequestHandler;

use App\Server\Request;

function RequestOfUser(
    User $user
): Request
{
    \assert(thereIsARequestOfUser(user: $user), 'There is no request of the user.');

    global $users_requests;

    return $users_requests[$user];
}

final class RequestOfUser
{
    public static function associate(
        Request $request,
        User $user
    ): void
    {
        \assert(thereIsNoRequestOfUser(user: $user), 'There is a request of the user.');

        global $users_requests, $requests_users;
        
        $users_requests[$user] = $request;

        $requests_users[$request] = $user;
    }
    
    public static function dissociate(
        Request $request,
        User $user
    ): void
    {
        \assert(requestIsOfUser(request: $request, user: $user), 'The request is not of the user.');

        global $users_requests, $requests_users;

        $request = $users_requests[$user];

        unset($users_requests[$user]);

        unset($requests_users[$request]);
    }
}