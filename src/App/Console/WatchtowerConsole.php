<?php

declare(strict_types=1);

namespace App\Console;

use Wedrix\Watchtower\Console as WatchtowerConsole;

use function App\DataStore;
use function App\Config;

function WatchtowerConsole(): WatchtowerConsole
{
    static $console;
    
    $console ??= new WatchtowerConsole(
        entityManager: DataStore(),
        schemaFileDirectory: (string) Config()->watchtowerSchemaFileDirectory(),
        schemaFileName: (string) Config()->watchtowerSchemaFileName(),
        pluginsDirectory: (string) Config()->watchtowerPluginsDirectory(),
        scalarTypeDefinitionsDirectory: (string) Config()->watchtowerScalarTypeDefinitionsDirectory(),
        cacheDirectory: (string) Config()->watchtowerCacheDirectory()
    );

    return $console;
}