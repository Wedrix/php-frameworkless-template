<?php

declare(strict_types=1);

namespace App;

use Twig\Environment;
use Twig\Loader\FilesystemLoader;

function TwigTemplateEngine(): Environment
{
    static $twigTemplateEngine;
    
    $twigTemplateEngine ??= (static function () {
        $twigLoader = new FilesystemLoader(EmailConfig()->templatesDirectory());

        return new Environment($twigLoader, [
            'debug' => AppConfig()->environment() === 'development',
            'cache' => EmailConfig()->templatesCacheDirectory(),
        ]);
    })();

    return $twigTemplateEngine;
}