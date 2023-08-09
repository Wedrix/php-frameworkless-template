<?php

declare(strict_types=1);

namespace App;

use Dotenv\Dotenv;

interface AppConfig
{
    public function name(): string;

    public function version(): string;

    public function environment(): string;

    public function domain(): string;

    public function endpoint(): string;

    public function serializationKey(): string;
}

function AppConfig(): AppConfig
{
    static $appConfig;
    
    $appConfig ??= new class() implements AppConfig {
        private readonly string $baseDirectory;

        /**
         * @var array<string,string|null>
         */
        private readonly array $configValues;

        private readonly string $name;
    
        private readonly string $version;
    
        private readonly string $environment;
    
        private readonly string $domain;
    
        private readonly string $endpoint;

        private readonly string $serializationKey;

        public function __construct()
        {
            $this->baseDirectory = \dirname(__FILE__, 3);

            $this->configValues = Dotenv::createArrayBacked(paths: $this->baseDirectory)->load();

            $this->name = $this->configValues['APP_NAME'] ?? throw new \Exception(
                message: 'The App name is not set. Try adding \'APP_NAME\' to the .env file.'
            );
    
            $this->version = (function (): string {
                $composerFileDirectory = $this->baseDirectory.'/composer.json';
    
                $composerData = \json_decode(
                    \file_get_contents($composerFileDirectory) 
                        ?: throw new \Exception("Error reading file: '$composerFileDirectory'. Kindly ensure it exists.")
                    , true
                );
                
                return $composerData['version'] 
                    ?? throw new \Exception("App version not set. Kindly set it in the composer.json file at '$composerFileDirectory'");
            })();
    
            $this->environment = (function (): string {
                $environment =  $this->configValues['APP_ENVIRONMENT'] ?? throw new \Exception(
                    message: 'The App environment is not set. Try adding \'APP_ENVIRONMENT\' to the .env file.'
                );
        
                if (!\in_array($environment, ['production', 'testing', 'development'])) {
                    throw new \Exception('The App environment is invalid. Must be either: production, testing, development.');
                }
        
                return $environment;
            })();
    
            $this->domain = $this->configValues['APP_DOMAIN'] ?? throw new \Exception(
                message: 'The App domain is not set. Try adding \'APP_DOMAIN\' to the .env file.'
            );
    
            $this->endpoint = $this->configValues['APP_ENDPOINT'] ?? throw new \Exception(
                message: 'The App endpoint is not set. Try adding \'APP_ENDPOINT\' to the .env file.'
            );

            $this->serializationKey = $this->configValues['APP_SERIALIZATION_KEY'] ?? throw new \Exception(
                message: 'The App serialization key is not set. Try add \'APP_SERIALIZATION_KEY\' to the .env file.'
            );
        }
    
        public function name(): string
        {
            return $this->name;
        }
    
        public function version(): string
        {
            return $this->version;
        }
    
        public function environment(): string
        {
            return $this->environment;
        }
    
        public function domain(): string
        {
            return $this->domain;
        }
    
        public function endpoint(): string
        {
            return $this->endpoint;
        }

        public function serializationKey(): string
        {
            return $this->serializationKey;
        }
    };

    return $appConfig;
}