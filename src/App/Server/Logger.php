<?php

declare(strict_types=1);

namespace App\Server;

use function App\Config;

interface Logger
{
    public function log(
        string $message
    ): void;
}

function Logger(): Logger
{
    static $logger;
    
    $logger ??= new class() implements Logger {
        public function log(
            string $message
        ): void
        {
            $logFile = Config()->serverLogFilesDirectory() . '/' . \date_create_immutable('now')->format('Y-m-d') . '.log';

            if ($fileHandle = \fopen($logFile, 'a')) {
                $maxAttempts = 10;
                $attempt = 0;

                while (!\flock($fileHandle, \LOCK_EX | \LOCK_NB)) {
                    if (++$attempt >= $maxAttempts) {
                        \fclose($fileHandle);

                        echo "Unable to secure lock for the log file '$logFile'."; // Print error instead of throwing another error, preventing infinite retries.
                    }

                    \usleep(100000);
                }

                \fwrite($fileHandle, "\r\n" . \date_create_immutable('now')->format('Y-m-d H:i:s') . $message);

                \flock($fileHandle, \LOCK_UN);

                \fclose($fileHandle);
            } 
            else {
                echo "Unable to open the log file '$logFile'."; // Print error instead of throwing another error, preventing infinite retries.
            }

        }
    };

    return $logger;
}