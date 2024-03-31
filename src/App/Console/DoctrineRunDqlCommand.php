<?php

declare(strict_types=1);

namespace App\Console;

use Doctrine\ORM\Tools\Console\Command\RunDqlCommand;
use Symfony\Component\Console\Command\Command;

use function App\DoctrineEntityManagerProvider;

function DoctrineRunDqlCommand(): Command
{
    static $doctrineRunDqlCommand;

    $doctrineRunDqlCommand ??= new RunDqlCommand(
        DoctrineEntityManagerProvider()
    );

    return $doctrineRunDqlCommand;
}