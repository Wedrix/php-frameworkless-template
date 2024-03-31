<?php

declare(strict_types=1);

namespace App\Console;

use Doctrine\ORM\Tools\Console\Command\InfoCommand;
use Symfony\Component\Console\Command\Command;

use function App\DoctrineEntityManagerProvider;

function DoctrineInfoCommand(): Command
{
    static $doctrineInfoCommand;

    $doctrineInfoCommand ??= new InfoCommand(
        DoctrineEntityManagerProvider()
    );

    return $doctrineInfoCommand;
}