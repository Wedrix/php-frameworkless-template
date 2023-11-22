<?php

declare(strict_types=1);

namespace App;

use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\Transport;

function SymfonyMailer(): Mailer
{
    static $symfonyMailer;

    $symfonyMailer ??= new Mailer(
        transport: Transport::fromDsn(
            dsn: Config()->symfonyMailerDsn()
        )
    );

    return $symfonyMailer;
}