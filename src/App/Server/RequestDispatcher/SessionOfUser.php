<?php

declare(strict_types=1);

namespace App\Server\RequestDispatcher;

function SessionOfUser(
    User $user
): Session
{
    global $users_sessions;

    if (thereIsNoSessionOfUser(user: $user)) {
        throw new \Exception('There is no session of the user.');
    }

    return $users_sessions[$user];
}

final class SessionOfUser
{
    public static function associate(
        Session $session,
        User $user
    ): void
    {
        global $users_sessions, $sessions_users;

        if (thereIsASessionOfUser(user: $user)) {
            throw new \Exception('There is a session of the user.');
        }

        $users_sessions[$user] = $session;

        $sessions_users[$session] = $user;
    }
    
    public static function dissociate(
        Session $session,
        User $user
    ): void
    {
        global $users_sessions, $sessions_users;

        if (sessionIsNotOfUser(session: $session, user: $user)) {
            throw new \Exception('The session is not of the user.');
        }
        
        $session = $users_sessions[$user];

        unset($users_sessions[$user]);

        unset($sessions_users[$session]);
    }
}