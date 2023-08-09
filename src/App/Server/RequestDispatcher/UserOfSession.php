<?php

declare(strict_types=1);

namespace App\Server\RequestDispatcher;

function UserOfSession(
    Session $session
): User
{
    global $sessions_users;

    if (!sessionHasAUser(session: $session)) {
        throw new \Exception('The session has no associated user.');
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

        if (sessionHasAUser(session: $session)) {
            $sessionUser = UserOfSession(session: $session);

            if ($sessionUser === $user) {
                throw new \Exception('The user is already associated to the session.');
            }

            throw new \Exception('The user is associated to a different session.');
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

        if (!sessionHasAUser(session: $session)) {
            throw new \Exception('The session has no associated user.');
        }

        if (!(UserOfSession(session: $session) === $user)) {
            throw new \Exception('The session is not associated to the user.');
        }
        
        $user = $sessions_users[$session];

        unset($sessions_users[$session]);

        unset($users_sessions[$user]);
    }
}