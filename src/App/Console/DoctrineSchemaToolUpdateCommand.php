<?php

declare(strict_types=1);

namespace App\Console;

use Doctrine\ORM\Tools\Console\Command\SchemaTool\UpdateCommand;
use Symfony\Component\Console\Command\Command;

use function App\DoctrineEntityManagerProvider;

function DoctrineSchemaToolUpdateCommand(): Command
{
    static $doctrineSchemaToolUpdateCommand;

    $doctrineSchemaToolUpdateCommand ??= new UpdateCommand(
        DoctrineEntityManagerProvider()
    );

    return $doctrineSchemaToolUpdateCommand;
}