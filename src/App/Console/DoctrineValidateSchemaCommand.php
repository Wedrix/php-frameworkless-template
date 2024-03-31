<?php

declare(strict_types=1);

namespace App\Console;

use Doctrine\ORM\Tools\Console\Command\ValidateSchemaCommand;
use Symfony\Component\Console\Command\Command;

use function App\DoctrineEntityManagerProvider;

function DoctrineValidateSchemaCommand(): Command
{
    static $doctrineValidateSchemaCommand;

    $doctrineValidateSchemaCommand ??= new ValidateSchemaCommand(
        DoctrineEntityManagerProvider()
    );

    return $doctrineValidateSchemaCommand;
}