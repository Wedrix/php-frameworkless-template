<?php

declare(strict_types=1);

namespace App;

use Dotenv\Dotenv;

interface EmailConfig
{
    public function templatesDirectory(): string;

    public function templatesCacheDirectory(): string;
}

function EmailConfig(): EmailConfig
{
    static $emailConfig;
    
    $emailConfig ??= new class() implements EmailConfig {
        private readonly string $baseDirectory;

        /**
         * @var array<string,string|null>
         */
        private readonly array $configValues;

        private readonly string $templatesDirectory;
    
        private readonly string $templatesCacheDirectory;

        public function __construct()
        {
            $this->baseDirectory = \dirname(__FILE__, 3);

            $this->configValues = Dotenv::createArrayBacked(paths: $this->baseDirectory)->load();

            $this->templatesDirectory = (function (): string {
                $templatesDirectory = $this->baseDirectory . ($this->configValues['EMAIL_TEMPLATES_DIRECTORY'] ?? throw new \Exception(
                    message: 'The email templates directory is not set. Try adding \'EMAIL_TEMPLATES_DIRECTORY\' to the .env file.'
                ));
        
                if (!\file_exists($templatesDirectory)) {
                    throw new \Exception('The email templates directory does not exist. Try setting a valid value using the relative path to the project root.');
                }
        
                return $templatesDirectory;
            })();
    
            $this->templatesCacheDirectory = $this->baseDirectory . ($this->configValues['EMAIL_TEMPLATES_CACHE_DIRECTORY'] ?? throw new \Exception(
                message: 'The email templates cache directory is not set. Try adding \'EMAIL_TEMPLATES_CACHE_DIRECTORY\' to the .env file.'
            ));
        }
    
        public function templatesDirectory(): string
        {
            return $this->templatesDirectory;
        }
    
        public function templatesCacheDirectory(): string
        {
            return $this->templatesCacheDirectory;
        }
    };

    return $emailConfig;
}