<?php

declare(strict_types=1);

namespace App\Console;

use Doctrine\ORM\Tools\Console\Command\MappingDescribeCommand;
use Symfony\Component\Console\Command\Command;

use function App\DoctrineEntityManagerProvider;

function DoctrineMappingDescribeCommand(): Command
{
    static $doctrineMappingDescribeCommand;

    $doctrineMappingDescribeCommand ??= new MappingDescribeCommand(
        DoctrineEntityManagerProvider()
    );

    return $doctrineMappingDescribeCommand;
}