<?php

declare(strict_types=1);

namespace App\Server\RequestHandler;

use App\Id;

interface User
{
    public function id(): ?Id;

    public function role(): ?string;

    //TODO: ... use cases go here
}

function User(
    ?Id $id,
    ?string $role
): User
{
    return new class(
        id: $id,
        role: $role
    ) implements User
    {
        public function __construct(
            private readonly Id $id,
            private readonly string $role
        ){}
        
        public function id(): Id
        {
            return $this->id;
        }

        public function role(): string
        {
            return $this->role;
        }
    };
}