<?php

declare(strict_types=1);

namespace App;
use Dotenv\Dotenv;

interface SymfonyMailerConfig
{
    public function dsn(): string;
}

function SymfonyMailerConfig(): SymfonyMailerConfig
{
    static $symfonyMailerConfig;

    $symfonyMailerConfig ??= new class() implements SymfonyMailerConfig {
        private readonly string $baseDirectory;

        /**
         * @var array<string,string|null>
         */
        private readonly array $configValues;
    
        private readonly string $dsn;

        public function __construct()
        {
            $this->baseDirectory = \dirname(__FILE__, 3);

            $this->configValues = Dotenv::createArrayBacked(paths: $this->baseDirectory)->load();

            $this->dsn = $this->configValues['SYMFONY_MAILER_DSN'] ?? throw new \Exception(
                message: 'The SymfonyMailer dsn is not set. Try adding \'SYMFONY_MAILER_DSN\' to the .env file.'
            );
        }

        public function dsn(): string
        {
            return $this->dsn;
        }
    };

    return $symfonyMailerConfig;
}