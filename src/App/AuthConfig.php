<?php

declare(strict_types=1);

namespace App;

use Dotenv\Dotenv;

interface AuthConfig
{
    public function signingKey(): string;

    public function signingAlgorithm(): string;

    public function accessTokenTTLInMinutes(): int;

    public function refreshTokenTTLInMinutes(): int;

    public function encryptionKey(): string;
}

function AuthConfig(): AuthConfig
{
    static $authConfig;
    
    $authConfig ??= new class() implements AuthConfig {
        private readonly string $baseDirectory;

        /**
         * @var array<string,string|null>
         */
        private readonly array $configValues;
    
        private readonly string $signingKey;
    
        private readonly string $signingAlgorithm;
    
        private readonly int $accessTokenTTLInMinutes;
    
        private readonly int $refreshTokenTTLInMinutes;
    
        private readonly string $encryptionKey;

        public function __construct()
        {
            $this->baseDirectory = \dirname(__FILE__, 3);

            $this->configValues = Dotenv::createArrayBacked(paths: $this->baseDirectory)->load();
    
            $this->signingKey = $this->configValues['AUTH_SIGNING_KEY'] ?? throw new \Exception(
                message: 'The Auth signing key is not set. Try adding \'AUTH_SIGNING_KEY\' to the .env file.'
            );
    
            $this->signingAlgorithm = $this->configValues['AUTH_SIGNING_ALGORITHM'] ?? throw new \Exception(
                message: 'The Auth signing algorithm is not set. Try adding \'AUTH_SIGNING_ALGORITHM\' to the .env file.'
            );
    
            $this->accessTokenTTLInMinutes = (function (): int {
                $ttl = $this->configValues['AUTH_ACCESS_TOKEN_TTL_MINUTES'] ?? throw new \Exception(
                    message: 'The Auth access token time-to-live is not set. Try adding \'AUTH_ACCESS_TOKEN_TTL_MINUTES\' to the .env file.'
                );
        
                if(!\ctype_digit($ttl)) {
                    throw new \Exception('The Auth access token time-to-live is invalid. Try seting a correct int value.');
                }
        
                return (int) $ttl;
            })();
    
            $this->refreshTokenTTLInMinutes = (function (): int {
                $ttl = $this->configValues['AUTH_REFRESH_TOKEN_TTL_MINUTES'] ?? throw new \Exception(
                    message: 'The Auth refresh token time-to-live is not set. Try adding \'AUTH_REFRESH_TOKEN_TTL_MINUTES\' to the .env file.'
                );
        
                if(!\ctype_digit($ttl)) {
                    throw new \Exception('The Auth refresh token time-to-live is invalid. Try seting a correct int value.');
                }
        
                return (int) $ttl;
            })();
    
            $this->encryptionKey = $this->configValues['AUTH_ENCRYPTION_KEY'] ?? throw new \Exception(
                message: 'The Auth encryption key is not set. Try adding \'AUTH_ENCRYPTION_KEY\' to the .env file.'
            );
        }
    
        public function signingKey(): string
        {
            return $this->signingKey;
        }
    
        public function signingAlgorithm(): string
        {
            return $this->signingAlgorithm;
        }
    
        public function accessTokenTTLInMinutes(): int
        {
            return $this->accessTokenTTLInMinutes;
        }
    
        public function refreshTokenTTLInMinutes(): int
        {
            return $this->refreshTokenTTLInMinutes;
        }
    
        public function encryptionKey(): string
        {
            return $this->encryptionKey;
        }
    };

    return $authConfig;
}