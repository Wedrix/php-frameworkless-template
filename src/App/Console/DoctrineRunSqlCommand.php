<?php

declare(strict_types=1);

namespace App\Console;

use Doctrine\DBAL\Tools\Console\Command\RunSqlCommand;
use Doctrine\ORM\Tools\Console\EntityManagerProvider\ConnectionFromManagerProvider;
use Symfony\Component\Console\Command\Command;

use function App\DoctrineEntityManagerProvider;

function DoctrineRunSqlCommand(): Command
{
    static $doctrineRunSqlCommand;

    $doctrineRunSqlCommand ??= new RunSqlCommand(
        new ConnectionFromManagerProvider(
            DoctrineEntityManagerProvider()
        )
    );

    return $doctrineRunSqlCommand;
}