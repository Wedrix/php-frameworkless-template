<?php

declare(strict_types=1);

namespace App;

use Laminas\Crypt\BlockCipher;

function Encrypter(): BlockCipher
{
    static $encrypter;
    
    $encrypter ??= (static function (): BlockCipher {
        $encrypter = BlockCipher::factory('openssl', ['algo' => 'aes']);

        $encrypter->setKey(AuthConfig()->encryptionKey());

        return $encrypter;
    })();

    return $encrypter;
}