<?php

declare(strict_types=1);

namespace App;

use Dotenv\Dotenv;

interface DatabaseConfig
{
    public function host(): string;

    public function port(): int;

    public function name(): string;

    public function user(): string;

    public function password(): string;
}

function DatabaseConfig(): DatabaseConfig
{
    static $databaseConfig;
    
    $databaseConfig ??= new class() implements DatabaseConfig {
        private readonly string $baseDirectory;

        /**
         * @var array<string,string|null>
         */
        private readonly array $configValues;

        private readonly string $host;
    
        private readonly int $port;
    
        private readonly string $name;
    
        private readonly string $user;
    
        private readonly string $password;

        public function __construct()
        {
            $this->baseDirectory = \dirname(__FILE__, 3);

            $this->configValues = Dotenv::createArrayBacked(paths: $this->baseDirectory)->load();

            $this->host = $this->configValues['DATABASE_HOST'] ?? throw new \Exception(
                message: 'The Database host is not set. Try adding \'DATABASE_HOST\' to the .env file.'
            );
    
            $this->port = (function (): int {
                $port =  $this->configValues['DATABASE_PORT'] ?? throw new \Exception(
                    message: 'The Database port is not set. Try adding \'DATABASE_PORT\' to the .env file.'
                );
        
                if(!\ctype_digit($port)) {
                    throw new \Exception('The Database port is invalid. Try seting a correct int value.');
                }
        
                return (int) $port;
            })();
    
            $this->name = $this->configValues['DATABASE_NAME'] ?? throw new \Exception(
                message: 'The Database name is not set. Try adding \'DATABASE_NAME\' to the .env file.'
            );
    
            $this->user = $this->configValues['DATABASE_USER'] ?? throw new \Exception(
                message: 'The Database user is not set. Try adding \'DATABASE_USER\' to the .env file.'
            );
    
            $this->password = $this->configValues['DATABASE_PASSWORD'] ?? throw new \Exception(
                message: 'The Database password is not set. Try adding \'DATABASE_PASSWORD\' to the .env file.'
            );
        }
    
        public function host(): string
        {
            return $this->host;
        }
    
        public function port(): int
        {
            return $this->port;
        }
    
        public function name(): string
        {
            return $this->name;
        }
    
        public function user(): string
        {
            return $this->user;
        }
    
        public function password(): string
        {
            return $this->password;
        }
    };

    return $databaseConfig;
}