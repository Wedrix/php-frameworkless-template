<?php

declare(strict_types=1);

namespace App\Console;

use Wedrix\Watchtower\Console as WatchtowerConsole;

use function App\DataMapper;
use function App\WatchtowerConfig;

function WatchtowerConsole(): WatchtowerConsole
{
    static $console;
    
    $console ??= new WatchtowerConsole(
        entityManager: DataMapper(),
        schemaFile: WatchtowerConfig()->schemaFile(),
        pluginsDirectory: WatchtowerConfig()->pluginsDirectory(),
        scalarTypeDefinitionsDirectory: WatchtowerConfig()->scalarTypeDefinitionsDirectory(),
        schemaCacheDirectory: WatchtowerConfig()->schemaCacheDirectory()
    );

    return $console;
}