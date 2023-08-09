<?php

declare(strict_types=1);

namespace App\Server\RequestDispatcher;

use App\CipherText;
use App\Hash;
use App\Id;

interface User
{
    public function id(): Id;

    public function role(): string;

    public function authorizationKey(): CipherText;

    public function password(): Hash;
}