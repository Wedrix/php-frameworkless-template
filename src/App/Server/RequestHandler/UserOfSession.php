<?php

declare(strict_types=1);

namespace App\Server\RequestHandler;

function UserOfSession(
    Session $session
): User
{
    \assert(thereIsAUserOfSession(session: $session), 'There is no user of the session.');

    global $sessions_users;

    return $sessions_users[$session];
}

final class UserOfSession
{
    public static function associate(
        User $user,
        Session $session
    ): void
    {
        \assert(thereIsNoUserOfSession(session: $session), 'There is a user of the session.');

        global $sessions_users, $users_sessions;

        $sessions_users[$session] = $user;

        $users_sessions[$user] = $session;
    }
    
    public static function dissociate(
        User $user,
        Session $session
    ): void
    {
        \assert(userIsOfSession(user: $user, session: $session), 'The user is not of the session.');

        global $sessions_users, $users_sessions;
        
        $user = $sessions_users[$session];

        unset($sessions_users[$session]);

        unset($users_sessions[$user]);
    }
}