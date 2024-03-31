<?php

declare(strict_types=1);

namespace App\Server\RequestHandler;

function SessionOfUser(
    User $user
): Session
{
    \assert(thereIsASessionOfUser(user: $user), 'There is no session of the user.');

    global $users_sessions;

    return $users_sessions[$user];
}

final class SessionOfUser
{
    public static function associate(
        Session $session,
        User $user
    ): void
    {
        \assert(thereIsNoSessionOfUser(user: $user), 'There is a session of the user.');

        global $users_sessions, $sessions_users;

        $users_sessions[$user] = $session;

        $sessions_users[$session] = $user;
    }
    
    public static function dissociate(
        Session $session,
        User $user
    ): void
    {
        \assert(sessionIsOfUser(session: $session, user: $user), 'The session is not of the user.');
        
        global $users_sessions, $sessions_users;
        
        $session = $users_sessions[$user];

        unset($users_sessions[$user]);

        unset($sessions_users[$session]);
    }
}