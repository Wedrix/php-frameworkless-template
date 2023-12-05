<?php

declare(strict_types=1);

namespace App\Server;

use Monolog\Formatter\LineFormatter;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

use function App\Config;

function Logger(): Logger
{
    static $logger;
    
    $logger ??= (static function(): Logger {
        $formatter = new LineFormatter("\n%datetime% >> %channel%:%level_name% >> %message%", "Y-m-d H:i:s");
        $stream = new StreamHandler(Config()->serverLogFileDirectory().'/'.Config()->serverLogFileName());
        $stream->setFormatter($formatter);
        $logger = new Logger('app');
        $logger->pushHandler($stream);

        return $logger;
    })();

    return $logger;
}