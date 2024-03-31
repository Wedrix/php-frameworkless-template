<?php

declare(strict_types=1);

namespace App\Console;

use Doctrine\ORM\Tools\Console\Command\ClearCache\EntityRegionCommand;
use Symfony\Component\Console\Command\Command;

use function App\DoctrineEntityManagerProvider;

function DoctrineClearCacheEntityRegionCommand(): Command
{
    static $doctrineClearCacheEntityRegionCommand;

    $doctrineClearCacheEntityRegionCommand ??= new EntityRegionCommand(
        DoctrineEntityManagerProvider()
    );

    return $doctrineClearCacheEntityRegionCommand;
}