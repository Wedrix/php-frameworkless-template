<?php

declare(strict_types=1);

namespace App;

use Dotenv\Dotenv;

interface IPAddressParserConfig
{
    public function attributeName(): string;

    public function checkProxyHeaders(): bool;

    /**
     * @return array<string>
     */
    public function headersToInspect(): array;

    /**
     * @return array<string>
     */
    public function trustedProxies(): array;
}

function IPAddressParserConfig(): IPAddressParserConfig
{
    static $ipAddressParserConfig;
    
    $ipAddressParserConfig ??= new class() implements IPAddressParserConfig {
        private readonly string $baseDirectory;

        /**
         * @var array<string,string|null>
         */
        private readonly array $configValues;
        
        private readonly string $attributeName;
    
        private readonly bool $checkProxyHeaders;
    
        /**
         * @var array<string>
         */
        private readonly array $headersToInspect;
    
        /**
         * @var array<string>
         */
        private readonly array $trustedProxies;

        public function __construct()
        {
            $this->baseDirectory = \dirname(__FILE__, 3);

            $this->configValues = Dotenv::createArrayBacked(paths: $this->baseDirectory)->load();

            $this->attributeName = $this->configValues['IP_ADDRESS_ATTRIBUTE_NAME'] ?? throw new \Exception(
                message: 'The ip addres attribute name is not set. Try adding \'IP_ADDRESS_ATTRIBUTE_NAME\' to the .env file.'
            );
    
            $this->checkProxyHeaders = $this->configValues['IP_ADDRESS_PARSER_CHECK_PROXY_HEADERS'] === 'true';
    
            $this->headersToInspect = \explode(',', $this->configValues['IP_ADDRESS_PARSER_HEADERS_TO_INSPECT'] ?? throw new \Exception(
                message: 'The ip addres headers to inspect is not set. Try adding \'IP_ADDRESS_PARSER_HEADERS_TO_INSPECT\' to the .env file.'
            ));
    
            $this->trustedProxies = \explode(',', $this->configValues['IP_ADDRESS_PARSER_TRUSTED_PROXIES'] ?? throw new \Exception(
                message: 'The ip addres trusted proxies is not set. Try adding \'IP_ADDRESS_PARSER_TRUSTED_PROXIES\' to the .env file.'
            ));
        }
    
        public function attributeName(): string
        {
            return $this->attributeName;
        }
    
        public function checkProxyHeaders(): bool
        {
            return $this->checkProxyHeaders;
        }

        public function headersToInspect(): array
        {
            return $this->headersToInspect;
        }

        public function trustedProxies(): array
        {
            return $this->trustedProxies;
        }
    };

    return $ipAddressParserConfig;
}