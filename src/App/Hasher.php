<?php

declare(strict_types=1);

namespace App;

use Laminas\Crypt\Password\Bcrypt;

function Hasher(): Bcrypt
{
    static $hasher;
    
    $hasher ??= new Bcrypt();

    return $hasher;
}