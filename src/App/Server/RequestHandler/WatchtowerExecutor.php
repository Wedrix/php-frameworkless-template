<?php

declare(strict_types=1);

namespace App\Server\RequestHandler;

use Wedrix\Watchtower\Executor;

use function App\Config;
use function App\DataStore;

function WatchtowerExecutor(): Executor
{
    static $watchtowerExecutor;
    
    $watchtowerExecutor ??= new Executor(
        entityManager: DataStore(),
        schemaFile: Config()->watchtowerSchemaFileDirectory().'/'.Config()->watchtowerSchemaFileName(),
        pluginsDirectory: (string) Config()->watchtowerPluginsDirectory(),
        scalarTypeDefinitionsDirectory: (string) Config()->watchtowerScalarTypeDefinitionsDirectory(),
        cacheDirectory: (string) Config()->watchtowerCacheDirectory(),
        optimize: Config()->appEnvironment() !== 'development'
    );

    return $watchtowerExecutor;
}