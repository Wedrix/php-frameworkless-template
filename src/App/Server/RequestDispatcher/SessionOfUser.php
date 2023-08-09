<?php

declare(strict_types=1);

namespace App\Server\RequestDispatcher;

function SessionOfUser(
    User $user
): Session
{
    global $users_sessions;

    if (!userHasASession(user: $user)) {
        throw new \Exception('The user is not associated to any session.');
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

        if (userHasASession(user: $user)) {
            $userSession = SessionOfUser(user: $user);

            if ($userSession === $session) {
                throw new \Exception('The session is already associated to the user.');
            }

            throw new \Exception('The session is associated to a different user.');
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

        if (!userHasASession(user: $user)) {
            throw new \Exception('The user has no associated session.');
        }

        if (!(SessionOfUser(user: $user) === $session)) {
            throw new \Exception('The user is not associated to the session.');
        }
        
        $session = $users_sessions[$user];

        unset($users_sessions[$user]);

        unset($sessions_users[$session]);
    }
}