<?php

declare(strict_types=1);

namespace App;

use Doctrine\ORM\Tools\Console\EntityManagerProvider\ConnectionFromManagerProvider;
use Doctrine\ORM\Tools\Console\EntityManagerProvider\SingleManagerProvider;
use Symfony\Component\Console\Application as Console;

use function App\Console\AddPluginWatchtowerCommand;
use function App\Console\AddScalarTypeDefinitionWatchtowerCommand;
use function App\Console\GenerateSchemaWatchtowerCommand;
use function App\Console\ListPluginsWatchtowerCommand;
use function App\Console\ListScalarTypeDefinitionsWatchtowerCommand;
use function App\Console\UpdateSchemaWatchtowerCommand;

function Console(): Console
{
    static $console;
    
    $console ??= (static function (): Console {
        $console = new Console(
            name: AppConfig()->name(),
            version: AppConfig()->version()
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
            AddPluginWatchtowerCommand(),
            ListPluginsWatchtowerCommand(),
            GenerateSchemaWatchtowerCommand(),
            UpdateSchemaWatchtowerCommand(),
            AddScalarTypeDefinitionWatchtowerCommand(),
            ListScalarTypeDefinitionsWatchtowerCommand(),

            //TODO: ... commands go here
        ]);

        return $console;
    })();

    return $console;
}