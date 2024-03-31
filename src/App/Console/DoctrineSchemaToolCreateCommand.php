<?php

declare(strict_types=1);

namespace App\Console;

use Doctrine\ORM\Tools\Console\Command\SchemaTool\CreateCommand;
use Symfony\Component\Console\Command\Command;

use function App\DoctrineEntityManagerProvider;

function DoctrineSchemaToolCreateCommand(): Command
{
    static $doctrineSchemaToolCreateCommand;

    $doctrineSchemaToolCreateCommand ??= new CreateCommand(
        DoctrineEntityManagerProvider()
    );

    return $doctrineSchemaToolCreateCommand;
}