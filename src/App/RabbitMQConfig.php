<?php

declare(strict_types=1);

namespace App;

use Dotenv\Dotenv;

interface RabbitMQConfig
{
    public function host(): string;

    public function port(): int;

    public function user(): string;

    public function password(): string;
}

function RabbitMQConfig(): RabbitMQConfig
{
    static $rabbitMQConfig;
    
    $rabbitMQConfig ??= new class() implements RabbitMQConfig {
        private readonly string $baseDirectory;

        /**
         * @var array<string,string|null>
         */
        private readonly array $configValues;

        private readonly string $host;

        private readonly int $port;
    
        private readonly string $user;
    
        private readonly string $password;

        public function __construct()
        {
            $this->baseDirectory = \dirname(__FILE__, 3);

            $this->configValues = Dotenv::createArrayBacked(paths: $this->baseDirectory)->load();

            $this->host = $this->configValues['RABBITMQ_HOST'] ?? throw new \Exception(
                message: 'The RabbitMQ host is not set. Try adding \'RABBITMQ_HOST\' to the .env file.'
            );
    
            $this->port = (function (): int {
                $port =  $this->configValues['RABBITMQ_PORT'] ?? throw new \Exception(
                    message: 'The RabbitMQ port is not set. Try adding \'RABBITMQ_PORT\' to the .env file.'
                );
        
                if(!\ctype_digit($port)) {
                    throw new \Exception('The RabbitMQ port is invalid. Try seting a correct int value.');
                }
        
                return (int) $port;
            })();
    
            $this->user = $this->configValues['RABBITMQ_USER'] ?? throw new \Exception(
                message: 'The RabbitMQ user is not set. Try adding \'RABBITMQ_USER\' to the .env file.'
            );
    
            $this->password = $this->configValues['RABBITMQ_PASSWORD'] ?? throw new \Exception(
                message: 'The RabbitMQ password is not set. Try adding \'RABBITMQ_PASSWORD\' to the .env file.'
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
    
        public function user(): string
        {
            return $this->user;
        }
    
        public function password(): string
        {
            return $this->password;
        }
    };

    return $rabbitMQConfig;
}