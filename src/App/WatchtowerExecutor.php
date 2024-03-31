<?php

declare(strict_types=1);

namespace App;

use function App\Config;
use function App\DoctrineEntityManager;

function WatchtowerExecutor(): _WatchtowerExecutor
{
    static $watchtowerExecutor;
    
    $watchtowerExecutor ??= new _WatchtowerExecutor(
        entityManager: DoctrineEntityManager(),
        schemaFile: Config()->watchtowerSchemaFileDirectory().'/'.Config()->watchtowerSchemaFileName(),
        pluginsDirectory: (string) Config()->watchtowerPluginsDirectory(),
        scalarTypeDefinitionsDirectory: (string) Config()->watchtowerScalarTypeDefinitionsDirectory(),
        cacheDirectory: (string) Config()->watchtowerCacheDirectory(),
        optimize: Config()->appEnvironment() !== 'development'
    );

    return $watchtowerExecutor;
}