<?php

declare(strict_types=1);

namespace App\Server\RequestHandler;

use App\Server\Request;

function UserOfRequest(
    Request $request
): User
{
    \assert(thereIsAUserOfRequest(request: $request), 'There is no user of the request.');

    global $requests_users;

    return $requests_users[$request];
}

final class UserOfRequest
{
    public static function associate(
        User $user,
        Request $request
    ): void
    {
        \assert(thereIsNoUserOfRequest(request: $request), 'There is a user of the request.');

        global $requests_users, $users_requests;
        
        $requests_users[$request] = $user;

        $users_requests[$user] = $request;
    }
    
    public static function dissociate(
        User $user,
        Request $request
    ): void
    {
        \assert(userIsOfRequest(user: $user, request: $request), 'The user is not of the request.');

        global $requests_users, $users_requests;

        $user = $requests_users[$request];

        unset($requests_users[$request]);

        unset($users_requests[$user]);
    }
}