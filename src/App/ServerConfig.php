<?php

declare(strict_types=1);

namespace App;

use Dotenv\Dotenv;

interface ServerConfig
{
    public function host(): string;

    public function port(): int;

    public function logFilePath(): string;
}

function ServerConfig(): ServerConfig
{
    static $serverConfig;
    
    $serverConfig ??= new class() implements ServerConfig {
        private readonly string $baseDirectory;

        /**
         * @var array<string,string|null>
         */
        private readonly array $configValues;

        private readonly string $host;
    
        private readonly int $port;
    
        private readonly string $logFilePath;

        public function __construct()
        {
            $this->baseDirectory = \dirname(__FILE__, 3);

            $this->configValues = Dotenv::createArrayBacked(paths: $this->baseDirectory)->load();

            $this->host = $this->configValues['SERVER_HOST'] ?? '0.0.0.0';
    
            $this->port = (function (): int {
                $port =  $this->configValues['SERVER_PORT'] ?? '80';
        
                if(!\ctype_digit($port)) {
                    throw new \Exception('The Server port is invalid. Try seting a correct int value.');
                }
        
                return (int) $port;
            })();
    
            $this->logFilePath = $this->baseDirectory . ($this->configValues['SERVER_LOG_FILE'] ?? throw new \Exception(
                message: 'The Server log file is not set. Try adding \'SERVER_LOG_FILE\' to the .env file.'
            ));
        }
    
        public function host(): string
        {
            return $this->host;
        }
    
        public function port(): int
        {
            return $this->port;
        }
    
        public function logFilePath(): string
        {
            return $this->logFilePath;
        }
    };

    return $serverConfig;
}