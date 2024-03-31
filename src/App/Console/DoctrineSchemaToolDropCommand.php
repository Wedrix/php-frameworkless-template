<?php

declare(strict_types=1);

namespace App\Console;

use Doctrine\ORM\Tools\Console\Command\SchemaTool\DropCommand;
use Symfony\Component\Console\Command\Command;

use function App\DoctrineEntityManagerProvider;

function DoctrineSchemaToolDropCommand(): Command
{
    static $doctrineSchemaToolDropCommand;

    $doctrineSchemaToolDropCommand ??= new DropCommand(
        DoctrineEntityManagerProvider()
    );

    return $doctrineSchemaToolDropCommand;
}