<?php

declare(strict_types=1);

namespace App\Console;

use Doctrine\ORM\Tools\Console\Command\ClearCache\QueryCommand;
use Symfony\Component\Console\Command\Command;

use function App\DoctrineEntityManagerProvider;

function DoctrineClearCacheQueryRegionCommand(): Command
{
    static $doctrineClearCacheQueryRegionCommand;

    $doctrineClearCacheQueryRegionCommand ??= new QueryCommand(
        DoctrineEntityManagerProvider()
    );

    return $doctrineClearCacheQueryRegionCommand;
}