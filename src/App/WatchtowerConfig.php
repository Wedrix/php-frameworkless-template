<?php

declare(strict_types=1);

namespace App;

use Dotenv\Dotenv;
use GraphQL\Error\DebugFlag;

interface WatchtowerConfig
{
    public function enableSecurityRules(): bool;

    public function debugFlag(): int;

    public function schemaFile(): string;

    public function schemaCacheDirectory(): string;

    public function pluginsDirectory(): string;

    public function scalarTypeDefinitionsDirectory(): string;
}

function WatchtowerConfig(): WatchtowerConfig
{
    static $watchtowerConfig;
    
    $watchtowerConfig ??= new class() implements WatchtowerConfig {
        private readonly string $baseDirectory;

        /**
         * @var array<string,string|null>
         */
        private readonly array $configValues;

        private readonly bool $enableSecurityRules;
    
        private readonly int $debugFlag;
    
        private readonly string $schemaFile;
    
        private readonly string $schemaCacheDirectory;
    
        private readonly string $pluginsDirectory;
    
        private readonly string $scalarTypeDefinitionsDirectory;

        public function __construct()
        {
            $this->baseDirectory = \dirname(__FILE__, 3);

            $this->configValues = Dotenv::createArrayBacked(paths: $this->baseDirectory)->load();

            $this->enableSecurityRules = (function (): bool {
                $enableSecurityRules = $this->configValues['WATCHTOWER_ENABLE_SECURITY_RULES'] ?? 'true';
        
                if (!\in_array($enableSecurityRules, ['true', 'false'], true)) {
                    throw new \Exception('The Watchtower \'enable security rules\' config option is invalid. Try either \'true\' or \'false\'.');
                }
        
                return $enableSecurityRules === 'true';
            })();
    
            $this->debugFlag = (AppConfig()->environment() === 'development')
                                    ? DebugFlag::INCLUDE_DEBUG_MESSAGE | DebugFlag::INCLUDE_TRACE
                                    : DebugFlag::NONE;
    
            $this->schemaFile = $this->baseDirectory . (
                $this->configValues['WATCHTOWER_SCHEMA_FILE'] 
                    ?? \DIRECTORY_SEPARATOR.'resources'.\DIRECTORY_SEPARATOR.'schema.graphql'
            );
    
            $this->schemaCacheDirectory = $this->baseDirectory . (
                $this->configValues['WATCHTOWER_SCHEMA_CACHE_DIRECTORY'] 
                    ?? \DIRECTORY_SEPARATOR.'var'.\DIRECTORY_SEPARATOR.'cache'.\DIRECTORY_SEPARATOR.'graphql'
            );
    
            $this->pluginsDirectory = $this->baseDirectory . (
                $this->configValues['WATCHTOWER_PLUGINS_DIRECTORY'] 
                    ?? \DIRECTORY_SEPARATOR.'config'.\DIRECTORY_SEPARATOR.'watchtower'.\DIRECTORY_SEPARATOR.'plugins'
            );
    
            $this->scalarTypeDefinitionsDirectory = $this->baseDirectory . (
                $this->configValues['WATCHTOWER_SCALAR_TYPE_DEFINITIONS_DIRECTORY'] 
                    ?? \DIRECTORY_SEPARATOR.'config'.\DIRECTORY_SEPARATOR.'watchtower'.\DIRECTORY_SEPARATOR.'scalar_type_definitions'
            );
        }
    
        public function enableSecurityRules(): bool
        {
            return $this->enableSecurityRules;
        }
    
        public function debugFlag(): int
        {
            return $this->debugFlag;
        }
    
        public function schemaFile(): string
        {
            return $this->schemaFile;
        }
    
        public function schemaCacheDirectory(): string
        {
            return $this->schemaCacheDirectory;
        }
    
        public function pluginsDirectory(): string
        {
            return $this->pluginsDirectory;
        }
    
        public function scalarTypeDefinitionsDirectory(): string
        {
            return $this->scalarTypeDefinitionsDirectory;
        }
    };

    return $watchtowerConfig;
}