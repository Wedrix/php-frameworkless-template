<?php

declare(strict_types=1);

namespace App\Console;

use Doctrine\ORM\Tools\Console\Command\ClearCache\CollectionRegionCommand;
use Symfony\Component\Console\Command\Command;

use function App\DoctrineEntityManagerProvider;

function DoctrineClearCacheCollectionRegionCommand(): Command
{
    static $doctrineClearCacheCollectionRegionCommand;

    $doctrineClearCacheCollectionRegionCommand ??= new CollectionRegionCommand(
        DoctrineEntityManagerProvider()
    );

    return $doctrineClearCacheCollectionRegionCommand;
}