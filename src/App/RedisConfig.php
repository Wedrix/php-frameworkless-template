<?php

declare(strict_types=1);

namespace App;

use Dotenv\Dotenv;

interface RedisConfig
{
    public function host(): string;

    public function port(): int;

    public function password(): string;
}

function RedisConfig(): RedisConfig
{
    static $redisConfig;
    
    $redisConfig ??= new class() implements RedisConfig {
        private readonly string $baseDirectory;

        /**
         * @var array<string,string|null>
         */
        private readonly array $configValues;

        private readonly string $host;
    
        private readonly int $port;
    
        private readonly string $password;

        public function __construct()
        {
            $this->baseDirectory = \dirname(__FILE__, 3);

            $this->configValues = Dotenv::createArrayBacked(paths: $this->baseDirectory)->load();

            $this->host = $this->configValues['REDIS_HOST'] ?? throw new \Exception(
                message: 'The Redis host is not set. Try adding \'REDIS_HOST\' to the .env file.'
            );
    
            $this->port = (function (): int {
                $port =  $this->configValues['REDIS_PORT'] ?? throw new \Exception(
                    message: 'The Redis port is not set. Try adding \'REDIS_PORT\' to the .env file.'
                );
        
                if(!\ctype_digit($port)) {
                    throw new \Exception('The Redis port is invalid. Try seting a correct int value.');
                }
        
                return (int) $port;
            })();
    
            $this->password = $this->configValues['REDIS_PASSWORD'] ?? throw new \Exception(
                message: 'The Redis password is not set. Try adding \'REDIS_PASSWORD\' to the .env file.'
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
    
        public function password(): string
        {
            return $this->password;
        }
    };

    return $redisConfig;
}