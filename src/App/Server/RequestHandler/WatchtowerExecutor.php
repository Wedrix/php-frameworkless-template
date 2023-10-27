<?php

declare(strict_types=1);

namespace App\Server\RequestHandler;

use Wedrix\Watchtower\Executor;

use function App\AppConfig;
use function App\DataStore;
use function App\WatchtowerConfig;

function WatchtowerExecutor(): Executor
{
    static $watchtowerExecutor;
    
    $watchtowerExecutor ??= new Executor(
        entityManager: DataStore(),
        schemaFile: WatchtowerConfig()->schemaFile(),
        pluginsDirectory: WatchtowerConfig()->pluginsDirectory(),
        scalarTypeDefinitionsDirectory: WatchtowerConfig()->scalarTypeDefinitionsDirectory(),
        cachesSchema: AppConfig()->environment() !== 'development',
        schemaCacheDirectory: WatchtowerConfig()->schemaCacheDirectory()
    );

    return $watchtowerExecutor;
}