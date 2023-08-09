<?php

declare(strict_types=1);

namespace App\Server\RequestDispatcher;

use Comet\Request;

function UserOfRequest(
    Request $request
): User
{
    global $requests_users;

    if (!requestHasAUser(request: $request)) {
        throw new \Exception('The request is not associated to any user.');
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

        if (requestHasAUser(request: $request)) {
            $requestUser = UserOfRequest(request: $request);

            if ($requestUser === $user) {
                throw new \Exception('The user is already associated to the request.');
            }

            throw new \Exception('The user is associated to a different request.');
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

        if (!requestHasAUser(request: $request)) {
            throw new \Exception('The request has no associated user.');
        }

        if (!(UserOfRequest(request: $request) === $user)) {
            throw new \Exception('The request is not associated to the user.');
        }

        $user = $requests_users[$request];

        unset($requests_users[$request]);

        unset($users_requests[$user]);
    }
}