<?php

declare(strict_types=1);

namespace App;

use Doctrine\ORM\Tools\Console\EntityManagerProvider\ConnectionFromManagerProvider;
use Doctrine\ORM\Tools\Console\EntityManagerProvider\SingleManagerProvider;
use Symfony\Component\Console\Application as Console;

use function App\Console\AddWatchtowerPluginCommand;
use function App\Console\AddWatchtowerScalarTypeDefinitionCommand;
use function App\Console\ClearServerLogsCommand;
use function App\Console\GenerateWatchtowerCacheCommand;
use function App\Console\GenerateWatchtowerSchemaCommand;
use function App\Console\ListWatchtowerPluginsCommand;
use function App\Console\ListWatchtowerScalarTypeDefinitionsCommand;
use function App\Console\UpdateWatchtowerSchemaCommand;

function Console(): Console
{
    static $console;
    
    $console ??= (static function(): Console {
        $console = new Console(
            name: Config()->appName(),
            version: Config()->appVersion()
        );

        $entityManagerProvider = new SingleManagerProvider(DataStore());
        $connectionProvider = new ConnectionFromManagerProvider($entityManagerProvider);

        $console->addCommands([
            // DBAL Commands
            new \Doctrine\DBAL\Tools\Console\Command\RunSqlCommand($connectionProvider),

            // ORM Commands
            new \Doctrine\ORM\Tools\Console\Command\ClearCache\CollectionRegionCommand($entityManagerProvider),
            new \Doctrine\ORM\Tools\Console\Command\ClearCache\EntityRegionCommand($entityManagerProvider),
            new \Doctrine\ORM\Tools\Console\Command\ClearCache\MetadataCommand($entityManagerProvider),
            new \Doctrine\ORM\Tools\Console\Command\ClearCache\QueryCommand($entityManagerProvider),
            new \Doctrine\ORM\Tools\Console\Command\ClearCache\QueryRegionCommand($entityManagerProvider),
            new \Doctrine\ORM\Tools\Console\Command\ClearCache\ResultCommand($entityManagerProvider),
            new \Doctrine\ORM\Tools\Console\Command\SchemaTool\CreateCommand($entityManagerProvider),
            new \Doctrine\ORM\Tools\Console\Command\SchemaTool\UpdateCommand($entityManagerProvider),
            new \Doctrine\ORM\Tools\Console\Command\SchemaTool\DropCommand($entityManagerProvider),
            new \Doctrine\ORM\Tools\Console\Command\GenerateProxiesCommand($entityManagerProvider),
            new \Doctrine\ORM\Tools\Console\Command\RunDqlCommand($entityManagerProvider),
            new \Doctrine\ORM\Tools\Console\Command\ValidateSchemaCommand($entityManagerProvider),
            new \Doctrine\ORM\Tools\Console\Command\InfoCommand($entityManagerProvider),
            new \Doctrine\ORM\Tools\Console\Command\MappingDescribeCommand($entityManagerProvider),

            // Watchtower Commands
            AddWatchtowerPluginCommand(),
            ListWatchtowerPluginsCommand(),
            GenerateWatchtowerSchemaCommand(),
            UpdateWatchtowerSchemaCommand(),
            AddWatchtowerScalarTypeDefinitionCommand(),
            ListWatchtowerScalarTypeDefinitionsCommand(),
            GenerateWatchtowerCacheCommand(),

            // Add Commands
            ClearServerLogsCommand(),
            //TODO: ... other commands go here
        ]);

        return $console;
    })();

    return $console;
}