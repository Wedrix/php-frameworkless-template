<?php

declare(strict_types=1);

namespace App
{
    use Symfony\Component\Console\Application as Console;

    use function App\Console\AllCommands;

    function Console(): Console
    {
        static $console;
        
        $console ??= (static function(): Console {
            $console = new Console(
                name: Config()->appName(),
                version: Config()->appVersion()
            );
    
            $console->addCommands(\iterator_to_array(AllCommands()));
    
            return $console;
        })();
    
        return $console;
    }
}

namespace App\Console
{
    use Symfony\Component\Console\Command\Command;
    
    /**
     * @var array<int,Command>
     */
    global $console_commands;

    $console_commands = [
        // Doctrine DBAL Commands
        DoctrineRunSqlCommand(),
        // Doctrine ORM Commands
        DoctrineClearCacheCollectionRegionCommand(),
        DoctrineClearCacheEntityRegionCommand(),
        DoctrineClearCacheMetadataCommand(),
        DoctrineClearCacheQueryCommand(),
        DoctrineClearCacheQueryRegionCommand(),
        DoctrineClearCacheResultCommand(),
        DoctrineSchemaToolCreateCommand(),
        DoctrineSchemaToolUpdateCommand(),
        DoctrineSchemaToolDropCommand(),
        DoctrineGenerateProxiesCommand(),
        DoctrineRunDqlCommand(),
        DoctrineValidateSchemaCommand(),
        DoctrineInfoCommand(),
        DoctrineMappingDescribeCommand(),
        // Watchtower Commands
        AddWatchtowerPluginCommand(),
        ListWatchtowerPluginsCommand(),
        GenerateWatchtowerSchemaCommand(),
        UpdateWatchtowerSchemaCommand(),
        AddWatchtowerScalarTypeDefinitionCommand(),
        ListWatchtowerScalarTypeDefinitionsCommand(),
        GenerateWatchtowerCacheCommand(),
        // App Commands
        ClearServerLogsCommand(),
        //TODO: Other commands go here ...
    ];
}