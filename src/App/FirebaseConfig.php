<?php

declare(strict_types=1);

namespace App;

use Dotenv\Dotenv;

interface FirebaseConfig
{
    public function signingKey(): string;

    public function serviceAccountEmail(): string;
}

function FirebaseConfig(): FirebaseConfig
{
    static $firebaseConfig;
    
    $firebaseConfig ??= new class() implements FirebaseConfig {
        private readonly string $baseDirectory;

        /**
         * @var array<string,string|null>
         */
        private readonly array $configValues;

        private readonly string $signingKey;
    
        private readonly string $serviceAccountEmail;

        public function __construct()
        {
            $this->baseDirectory = \dirname(__FILE__, 3);

            $this->configValues = Dotenv::createArrayBacked(paths: $this->baseDirectory)->load();
            
            $this->signingKey = $this->configValues['FIREBASE_SIGNING_KEY'] ?? throw new \Exception(
                message: 'The Firebase signing key is not set. Try adding \'FIREBASE_SIGNING_KEY\' to the .env file.'
            );
    
            $this->serviceAccountEmail = $this->configValues['FIREBASE_SERVICE_ACCOUNT_EMAIL'] ?? throw new \Exception(
                message: 'The Firebase service account email is not set. Try adding \'FIREBASE_SERVICE_ACCOUNT_EMAIL\' to the .env file.'
            );
        }
    
        public function signingKey(): string
        {
            return $this->signingKey;
        }
    
        public function serviceAccountEmail(): string
        {
            return $this->serviceAccountEmail;
        }
    };

    return $firebaseConfig;
}