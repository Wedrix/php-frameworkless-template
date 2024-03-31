<?php

declare(strict_types=1);

namespace App\Console;

use Doctrine\ORM\Tools\Console\Command\ClearCache\MetadataCommand;
use Symfony\Component\Console\Command\Command;

use function App\DoctrineEntityManagerProvider;

function DoctrineClearCacheMetadataCommand(): Command
{
    static $doctrineClearCacheMetadataCommand;

    $doctrineClearCacheMetadataCommand ??= new MetadataCommand(
        DoctrineEntityManagerProvider()
    );

    return $doctrineClearCacheMetadataCommand;
}