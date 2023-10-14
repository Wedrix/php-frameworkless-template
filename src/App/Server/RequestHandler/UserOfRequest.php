<?php

declare(strict_types=1);

namespace App\Server\RequestHandler;

use App\Server\Request;

function UserOfRequest(
    Request $request
): User
{
    global $requests_users;

    if (thereIsNoUserOfRequest(request: $request)) {
        throw new \Exception('There is no user of the request.');
    }

    return $requests_users[$request];
}

final class UserOfRequest
{
    public static function associate(
        User $user,
        Request $request
    ): void
    {
        global $requests_users, $users_requests;

        if (thereIsAUserOfRequest(request: $request)) {
            throw new \Exception('There is a user of the request.');
        }
        
        $requests_users[$request] = $user;

        $users_requests[$user] = $request;
    }
    
    public static function dissociate(
        User $user,
        Request $request
    ): void
    {
        global $requests_users, $users_requests;

        if (userIsNotOfRequest(user: $user, request: $request)) {
            throw new \Exception('The user is not of the request.');
        }

        $user = $requests_users[$request];

        unset($requests_users[$request]);

        unset($users_requests[$user]);
    }
}