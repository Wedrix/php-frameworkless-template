<?php

declare(strict_types=1);

namespace App\Server\RequestDispatcher;

function UserOfSession(
    Session $session
): User
{
    global $sessions_users;

    if (thereIsNoUserOfSession(session: $session)) {
        throw new \Exception('There is no user of the session.');
    }

    return $sessions_users[$session];
}

final class UserOfSession
{
    public static function associate(
        User $user,
        Session $session
    ): void
    {
        global $sessions_users, $users_sessions;

        if (thereIsAUserOfSession(session: $session)) {
            throw new \Exception('There is a user of the session.');
        }

        $sessions_users[$session] = $user;

        $users_sessions[$user] = $session;
    }
    
    public static function dissociate(
        User $user,
        Session $session
    ): void
    {
        global $sessions_users, $users_sessions;

        if (userIsNotOfSession(user: $user, session: $session)) {
            throw new \Exception('The user is not of the session.');
        }
        
        $user = $sessions_users[$session];

        unset($sessions_users[$session]);

        unset($users_sessions[$user]);
    }
}