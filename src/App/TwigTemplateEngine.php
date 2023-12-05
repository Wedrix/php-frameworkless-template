<?php

declare(strict_types=1);

namespace App;

use Twig\Environment;
use Twig\Loader\FilesystemLoader;

function TwigTemplateEngine(): Environment
{
    static $twigTemplateEngine;
    
    $twigTemplateEngine ??= (static function() {
        return new Environment(
            loader: new FilesystemLoader((string) Config()->emailTemplatesDirectory()), 
            options: [
                'debug' => Config()->appEnvironment() === 'development',
                'cache' => (string) Config()->emailTemplatesCacheDirectory(),
                'optimizations' => Config()->appEnvironment() === 'development' ? 0 : -1
            ],
        );
    })();

    return $twigTemplateEngine;
}