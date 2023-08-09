<?php

declare(strict_types=1);

namespace App;

interface EmailClient
{
    public function sendEmail(
        Email $email
    ): void;
}

function EmailClient(): EmailClient
{
    static $emailClient;

    /**
     * Needed since PHP cannot serialize Anonymous Classes (as of v8.2).
     */
    $emailClient ??= new _SerializableEmailClient();
    
    return $emailClient;
}