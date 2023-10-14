<?php

declare(strict_types=1);

namespace App\Server\RequestHandler;

use App\Server\Request;

function RequestOfUser(
    User $user
): Request
{
    global $users_requests;

    if (thereIsNoRequestOfUser(user: $user)) {
        throw new \Exception('There is no request of the user.');
    }

    return $users_requests[$user];
}

final class RequestOfUser
{
    public static function associate(
        Request $request,
        User $user
    ): void
    {
        global $users_requests, $requests_users;

        if (thereIsARequestOfUser(user: $user)) {
            throw new \Exception('There is a request of the user.');
        }
        
        $users_requests[$user] = $request;

        $requests_users[$request] = $user;
    }
    
    public static function dissociate(
        Request $request,
        User $user
    ): void
    {
        global $users_requests, $requests_users;

        if (requestIsNotOfUser(request: $request, user: $user)) {
            throw new \Exception('The request is not of the user.');
        }

        $request = $users_requests[$user];

        unset($users_requests[$user]);

        unset($requests_users[$request]);
    }
}