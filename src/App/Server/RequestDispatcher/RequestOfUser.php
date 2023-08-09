<?php

declare(strict_types=1);

namespace App\Server\RequestDispatcher;

use Comet\Request;

function RequestOfUser(
    User $user
): Request
{
    global $users_requests;

    if (!userHasARequest(user: $user)) {
        throw new \Exception('The user is not associated to any request.');
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

        if (userHasARequest(user: $user)) {
            $userRequest = RequestOfUser(user: $user);

            if ($userRequest === $request) {
                throw new \Exception('The request is already associated to the user.');
            }

            throw new \Exception('The request is associated to a different user.');
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

        if (!userHasARequest(user: $user)) {
            throw new \Exception('The user has no associated request.');
        }

        if (!(RequestOfUser(user: $user) === $request)) {
            throw new \Exception('The user is not associated to the request.');
        }

        $request = $users_requests[$user];

        unset($users_requests[$user]);

        unset($requests_users[$request]);
    }
}