<?php

declare(strict_types=1);

namespace App\Console;

use Doctrine\ORM\Tools\Console\Command\GenerateProxiesCommand;
use Symfony\Component\Console\Command\Command;

use function App\DoctrineEntityManagerProvider;

function DoctrineGenerateProxiesCommand(): Command
{
    static $doctrineGenerateProxiesCommand;

    $doctrineGenerateProxiesCommand ??= new GenerateProxiesCommand(
        DoctrineEntityManagerProvider()
    );

    return $doctrineGenerateProxiesCommand;
}