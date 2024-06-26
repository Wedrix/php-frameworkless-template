<?php

declare(strict_types=1);

namespace App;

use Wedrix\Watchtower\Console as WatchtowerConsole;

use function App\DoctrineEntityManager;
use function App\Config;

function WatchtowerConsole(): WatchtowerConsole
{
    static $console;
    
    $console ??= new WatchtowerConsole(
        entityManager: DoctrineEntityManager(),
        schemaFileDirectory: (string) Config()->watchtowerSchemaFileDirectory(),
        schemaFileName: (string) Config()->watchtowerSchemaFileName(),
        pluginsDirectory: (string) Config()->watchtowerPluginsDirectory(),
        scalarTypeDefinitionsDirectory: (string) Config()->watchtowerScalarTypeDefinitionsDirectory(),
        cacheDirectory: (string) Config()->watchtowerCacheDirectory()
    );

    return $console;
}