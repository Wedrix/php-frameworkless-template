<?php

declare(strict_types=1);

namespace App\Console;

use Doctrine\ORM\Tools\Console\Command\ClearCache\ResultCommand;
use Symfony\Component\Console\Command\Command;

use function App\DoctrineEntityManagerProvider;

function DoctrineClearCacheResultCommand(): Command
{
    static $doctrineClearCacheResultCommand;

    $doctrineClearCacheResultCommand ??= new ResultCommand(
        DoctrineEntityManagerProvider()
    );

    return $doctrineClearCacheResultCommand;
}