<?php

declare(strict_types=1);

namespace App;

use Wedrix\Watchtower\Executor as WatchtowerExecutor;

use function App\Config;
use function App\DataStore;

function WatchtowerExecutor(): WatchtowerExecutor
{
    static $watchtowerExecutor;
    
    $watchtowerExecutor ??= new WatchtowerExecutor(
        entityManager: DataStore(),
        schemaFile: Config()->watchtowerSchemaFileDirectory().'/'.Config()->watchtowerSchemaFileName(),
        pluginsDirectory: (string) Config()->watchtowerPluginsDirectory(),
        scalarTypeDefinitionsDirectory: (string) Config()->watchtowerScalarTypeDefinitionsDirectory(),
        cacheDirectory: (string) Config()->watchtowerCacheDirectory(),
        optimize: Config()->appEnvironment() !== 'development'
    );

    return $watchtowerExecutor;
}