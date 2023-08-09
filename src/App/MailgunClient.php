<?php

declare(strict_types=1);

namespace App;

use Mailgun\Mailgun;

function MailgunClient(): Mailgun
{
    static $mailgunClient;
    
    $mailgunClient ??= Mailgun::create(MailgunConfig()->apiKey());

    return $mailgunClient;
}