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
            'cache' => EmailConfig()->templatesCacheDirectory(),
        ]);
    })();

    return $twigTemplateEngine;
}