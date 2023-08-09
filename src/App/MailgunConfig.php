<?php

declare(strict_types=1);

namespace App;

use Dotenv\Dotenv;

interface MailgunConfig
{
    public function domain(): string;

    public function apiKey(): string;
}

function MailgunConfig(): MailgunConfig
{
    static $mailgunConfig;
    
    $mailgunConfig ??= new class() implements MailgunConfig {
        private readonly string $baseDirectory;

        /**
         * @var array<string,string|null>
         */
        private readonly array $configValues;

        private readonly string $domain;
    
        private readonly string $apiKey;

        public function __construct()
        {
            $this->baseDirectory = \dirname(__FILE__, 3);

            $this->configValues = Dotenv::createArrayBacked(paths: $this->baseDirectory)->load();
            
            $this->domain = $this->configValues['MAILGUN_DOMAIN'] ?? throw new \Exception(
                message: 'The Mailgun domain is not set. Try adding \'MAILGUN_DOMAIN\' to the .env file.'
            );
    
            $this->apiKey = $this->configValues['MAILGUN_API_KEY'] ?? throw new \Exception(
                message: 'The Mailgun api key is not set. Try adding \'MAILGUN_API_KEY\' to the .env file.'
            );
        }
    
        public function domain(): string
        {
            return $this->domain;
        }
    
        public function apiKey(): string
        {
            return $this->apiKey;
        }
    };

    return $mailgunConfig;
}