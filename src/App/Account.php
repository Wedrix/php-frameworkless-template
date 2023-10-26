<?php

declare(strict_types=1);

namespace App;

interface Account
{
    public function id(): Id;

    public function emailAddress(): EmailAddress;

    public function password(): Hash;

    public function authorizationKey(): CipherText;

    public function changeAuthorizationKey(): void;

    public function changePassword(
        Hash $password
    ): void;
}

// TODO: Implement method
function Account(
    Id $id,
    EmailAddress $emailAddress,
    Hash $password,
    CipherText $authorizationKey
): Account
{
    throw new \Exception('Unimplemented method.');
}