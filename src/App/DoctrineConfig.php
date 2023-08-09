<?php

declare(strict_types=1);

namespace App;

use Dotenv\Dotenv;

interface DoctrineConfig
{
    /**
     * @return array<string>
     */
    public function paths(): array;

    public function driver(): string;

    /**
     * @return array{
     *      driver: string, 
     *      host: string, 
     *      user: string, 
     *      password: string, 
     *      dbname: string, 
     *      port: int
     * }
     */
    public function connection(): array;

    public function isDevMode(): bool;

    public function proxyDirectory(): string;
}

function DoctrineConfig(): DoctrineConfig
{
    static $doctrineConfig;
    
    $doctrineConfig ??= new class() implements DoctrineConfig {
        private readonly string $baseDirectory;

        /**
         * @var array<string,string|null>
         */
        private readonly array $configValues;
        
        /**
         * @var array<string>
         */
        private readonly array $paths;
    
        private readonly string $driver;
    
        /**
         * @var array{
         *      driver: string, 
         *      host: string, 
         *      user: string, 
         *      password: string, 
         *      dbname: string, 
         *      port: int
         * }
         */
        private readonly array $connection;
    
        private readonly bool $isDevMode;
    
        private readonly string $proxyDirectory;

        public function __construct()
        {
            $this->baseDirectory = \dirname(__FILE__, 3);

            $this->configValues = Dotenv::createArrayBacked(paths: $this->baseDirectory)->load();

            $this->paths = (function (): array {
                $modelsDirectory = $this->baseDirectory . ($this->configValues['DOCTRINE_MODELS_DIRECTORY'] ?? throw new \Exception(
                    message: 'The Doctrine models directory is not set. Try adding \'DOCTRINE_MODELS_DIRECTORY\' to the .env file.'
                ));
        
                if (!\file_exists($modelsDirectory)) {
                    throw new \Exception('The Doctrine models directory is invalid. Try setting a valid value using the relative path to the project root.');
                }
        
                return [$modelsDirectory];
            })();
    
            $this->driver = (function (): string {
                $driver = $this->configValues['DOCTRINE_DB_DRIVER'] ?? throw new \Exception(
                    message: 'The Doctrine driver is not set. Try adding \'DOCTRINE_DB_DRIVER\' to the .env file.'
                );
        
                if (!\in_array($driver, ['pdo_mysql', 'pdo_sqlite', 'pdo_pgsql', 'pdo_sqlsrv', 'sqlsrv', 'oci8'])) {
                    throw new \Exception('The Doctrine driver is invalid. Kindly set a proper driver from the Documentation.');
                }
        
                return $driver;
            })();
    
            $this->connection = [
                'driver' => $this->driver,
                'host' => DatabaseConfig()->host(),
                'user' => DatabaseConfig()->user(),
                'password' => DatabaseConfig()->password(),
                'dbname' => DatabaseConfig()->name(),
                'port' => DatabaseConfig()->port(),
            ];
    
            $this->isDevMode = AppConfig()->environment() === 'development';
    
            $this->proxyDirectory = $this->baseDirectory . ($this->configValues['DOCTRINE_PROXIES_DIRECTORY'] ?? throw new \Exception(
                message: 'The Doctrine proxies directory is not set. Try adding \'DOCTRINE_PROXIES_DIRECTORY\' to the .env file.'
            ));
        }

        public function paths(): array
        {
            return $this->paths;
        }
    
        public function driver(): string
        {
            return $this->driver;
        }

        public function connection(): array
        {
            return $this->connection;
        }
    
        public function isDevMode(): bool
        {
            return $this->isDevMode;
        }
    
        public function proxyDirectory(): string
        {
            return $this->proxyDirectory;
        }
    };

    return $doctrineConfig;
}