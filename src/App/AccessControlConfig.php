<?php

declare(strict_types=1);

namespace App;

use Dotenv\Dotenv;

interface AccessControlConfig
{
    /**
     * @return array<string>
     */
    public function allowedOrigins(): array;

    /**
     * @return array<string>
     */
    public function allowedHeaders(): array;

    /**
     * @return array<string>
     */
    public function allowedMethods(): array;

    /**
     * @return array<string>
     */
    public function exposeHeaders(): array;

    public function allowCredentials(): bool;

    public function apiAccessLimit(): int;

    public function apiAccessWindow(): int;

    public function apiAccessWindowGrowthFactor(): int;

    public function apiAccessWindowMaxSize(): int;
}

function AccessControlConfig(): AccessControlConfig
{
    static $accessControlConfig;
    
    $accessControlConfig ??= new class() implements AccessControlConfig {
        private readonly string $baseDirectory;

        /**
         * @var array<string,string|null>
         */
        private readonly array $configValues;

        /**
         * @var array<string>
         */
        private readonly array $allowedOrigins;
    
        /**
         * @var array<string>
         */
        private readonly array $allowedHeaders;
    
        /**
         * @var array<string>
         */
        private readonly array $allowedMethods;
    
        /**
         * @var array<string>
         */
        private readonly array $exposeHeaders;
    
        private readonly bool $allowCredentials;
    
        private readonly int $apiAccessLimit;
    
        private readonly int $apiAccessWindow;
    
        private readonly int $apiAccessWindowGrowthFactor;
    
        private readonly int $apiAccessWindowMaxSize;

        public function __construct()
        {
            $this->baseDirectory = \dirname(__FILE__, 3);

            $this->configValues = Dotenv::createArrayBacked(paths: $this->baseDirectory)->load();

            $this->allowedOrigins = \explode(',', $this->configValues['ACCESS_CONTROL_ALLOWED_ORIGINS'] ?? throw new \Exception(
                message: 'The Access Control allowed origins is not set. Try adding \'ACCESS_CONTROL_ALLOWED_ORIGINS\' to the .env file.'
            ));
    
            $this->allowedHeaders = \explode(',', $this->configValues['ACCESS_CONTROL_ALLOWED_HEADERS'] ?? throw new \Exception(
                message: 'The Access Control allowed headers is not set. Try adding \'ACCESS_CONTROL_ALLOWED_HEADERS\' to the .env file.'
            ));
    
            $this->allowedMethods = (function (): array {
                $allowedMethods = \explode(',', $this->configValues['ACCESS_CONTROL_ALLOWED_METHODS'] ?? throw new \Exception(
                    message: 'The Access Control allowed methods is not set. Try adding \'ACCESS_CONTROL_ALLOWED_METHODS\' to the .env file.'
                ));
        
                $httpMethods = ['GET','HEAD','POST','PUT','DELETE','CONNECT','OPTIONS','TRACE','PATCH'];
        
                if (!\all_in_array($allowedMethods, $httpMethods)) {
                    throw new \Exception('Invalid access controle methods. Must be a subset of '.\implode(',', $httpMethods));
                }
        
                return $allowedMethods;
            })();
    
            $this->exposeHeaders = (function (): array {
                $exposeHeaders = $this->configValues['ACCESS_CONTROL_EXPOSE_HEADERS'];
        
                if (!\is_null($exposeHeaders)) {
                    return \explode(',', $exposeHeaders);
                }
        
                return [];
            })();
    
            $this->allowCredentials = $this->configValues['ACCESS_CONTROL_ALLOW_CREDENTIALS'] === 'true';
    
            $this->apiAccessLimit = (function (): int {
                $apiAccessLimit = $this->configValues['ACCESS_CONTROL_API_ACCESS_LIMIT'] ?? throw new \Exception(
                    message: 'The Access Control api access limit is not set. Try adding \'ACCESS_CONTROL_API_ACCESS_LIMIT\' to the .env file.'
                );
        
                if(!\ctype_digit($apiAccessLimit)) {
                    throw new \Exception('The Access Control api access limit is invalid. Try seting a correct int value.');
                }
        
                return (int) $apiAccessLimit;
            })();
    
            $this->apiAccessWindow = (function (): int {
                $apiAccessWindow = $this->configValues['ACCESS_CONTROL_API_ACCESS_WINDOW'] ?? throw new \Exception(
                    message: 'The Access Control api access window is not set. Try adding \'ACCESS_CONTROL_API_ACCESS_WINDOW\' to the .env file.'
                );
        
                if(!\ctype_digit($apiAccessWindow)) {
                    throw new \Exception('The Access Control api access window is invalid. Try seting a correct int value.');
                }
        
                return (int) $apiAccessWindow;
            })();
    
            $this->apiAccessWindowGrowthFactor = (function (): int {
                $apiAccessWindowGrowthFactor = $this->configValues['ACCESS_CONTROL_API_ACCESS_WINDOW_GROWTH_FACTOR'] ?? throw new \Exception(
                    message: 'The Access Control api access window growth factor is not set. Try adding \'ACCESS_CONTROL_API_ACCESS_WINDOW_GROWTH_FACTOR\' to the .env file.'
                );
        
                if(!\ctype_digit($apiAccessWindowGrowthFactor)) {
                    throw new \Exception('The Access Control api access window growth factor is invalid. Try seting a correct int value.');
                }
        
                return (int) $apiAccessWindowGrowthFactor;
            })();
    
            $this->apiAccessWindowMaxSize = (function (): int {
                $apiAccessWindowMaxSize = $this->configValues['ACCESS_CONTROL_API_ACCESS_WINDOW_MAX_SIZE'] ?? throw new \Exception(
                    message: 'The Access Control api access window max size is not set. Try adding \'ACCESS_CONTROL_API_ACCESS_WINDOW_MAX_SIZE\' to the .env file.'
                );
        
                if(!\ctype_digit($apiAccessWindowMaxSize)) {
                    throw new \Exception('The Access Control api access window max size is invalid. Try seting a correct int value.');
                }
        
                return (int) $apiAccessWindowMaxSize;
            })();
        }

        public function allowedOrigins(): array
        {
            return $this->allowedOrigins;
        }

        public function allowedHeaders(): array
        {
            return $this->allowedHeaders;
        }

        public function allowedMethods(): array
        {
            return $this->allowedMethods;
        }

        public function exposeHeaders(): array
        {
            return $this->exposeHeaders;
        }
    
        public function allowCredentials(): bool
        {
            return $this->allowCredentials;
        }
    
        public function apiAccessLimit(): int
        {
            return $this->apiAccessLimit;
        }
    
        public function apiAccessWindow(): int
        {
            return $this->apiAccessWindow;
        }
    
        public function apiAccessWindowGrowthFactor(): int
        {
            return $this->apiAccessWindowGrowthFactor;
        }
    
        public function apiAccessWindowMaxSize(): int
        {
            return $this->apiAccessWindowMaxSize;
        }
    };

    return $accessControlConfig;
}