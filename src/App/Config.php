<?php

declare(strict_types=1);

namespace App;

use Dotenv\Dotenv;

interface Config
{
    public function appBaseDirectory(): DirectoryPath;

    /**
     * @return array<int,string> Map of standard HTTP status code/reason phrases
     */
    public function appHttpPhrases(): array;

    /**
     * @return array<int,string> Map of standard ports and supported schemes
     */
    public function appUriSchemes(): array;

    public function appName(): string;

    public function appVersion(): string;

    public function appEnvironment(): string;

    public function appDomain(): string;

    public function appEndpoint(): string;

    public function appSerializationKey(): string;

    public function serverHost(): string;

    public function serverPort(): int;

    public function serverLogFileDirectory(): DirectoryPath;

    public function serverLogFileName(): FileName;
    
    public function authSigningKey(): string;

    public function authSigningAlgorithm(): string;

    public function authAccessTokenTTLInMinutes(): int;

    public function authRefreshTokenTTLInMinutes(): int;

    public function authEncryptionKey(): string;

    public function authFingerprintHashAlgorithm(): string;

    /**
     * @return int<1,max>
     */
    public function authKeyLength(): int;

    public function accessControlMaxQueryDepth(): int;

    /**
     * @return array<string>
     */
    public function accessControlAllowedOrigins(): array;

    /**
     * @return array<string>
     */
    public function accessControlAllowedHeaders(): array;

    /**
     * @return array<string>
     */
    public function accessControlAllowedMethods(): array;

    /**
     * @return array<string>
     */
    public function accessControlExposeHeaders(): array;

    public function accessControlAllowCredentials(): bool;

    public function accessControlApiAccessLimit(): int;

    public function accessControlApiAccessWindowSizeInSeconds(): int;

    /**
     * @return int<1,max>
     */
    public function accessControlUserContextKeyLength(): int;

    public function databaseHost(): string;

    public function databasePort(): int;

    public function databaseName(): string;

    public function databaseUser(): string;

    public function databasePassword(): string;

    public function doctrineModelsDirectories(): DirectoryPaths;

    public function doctrineDBDriver(): string;

    /**
     * @return array<string,mixed>
     */
    public function doctrineConnection(): array;

    public function doctrineIsDevMode(): bool;

    public function doctrineProxiesDirectory(): DirectoryPath;

    public function watchtowerSchemaFileDirectory(): DirectoryPath;

    public function watchtowerSchemaFileName(): FileName;

    public function watchtowerCacheDirectory(): DirectoryPath;

    public function watchtowerPluginsDirectory(): DirectoryPath;

    public function watchtowerScalarTypeDefinitionsDirectory(): DirectoryPath;

    public function emailTemplatesDirectory(): DirectoryPath;

    public function emailTemplatesCacheDirectory(): DirectoryPath;

    public function symfonyMailerDsn(): string;

    public function rabbitMQHost(): string;

    public function rabbitMQPort(): int;

    public function rabbitMQUser(): string;

    public function rabbitMQPassword(): string;

    public function redisHost(): string;

    public function redisPort(): int;

    public function redisPassword(): string;

    public function ipAddressParserAttributeName(): string;

    public function ipAddressParserCheckProxyHeaders(): bool;

    /**
     * @return array<string>
     */
    public function ipAddressParserHeadersToInspect(): array;

    /**
     * @return array<string>
     */
    public function ipAddressParserTrustedProxies(): array;
}

function Config(): Config
{
    static $config;
    
    $config ??= new class() implements Config {
        private readonly DirectoryPath $appBaseDirectory;

        /**
         * @var array<int,string>
         */
        private readonly array $appHttpPhrases;
    
        /**
         * @var array<int,string>
         */
        private readonly array $appUriSchemes;

        private readonly string $appName;
    
        private readonly string $appVersion;
    
        private readonly string $appEnvironment;
    
        private readonly string $appDomain;
    
        private readonly string $appEndpoint;

        private readonly string $appSerializationKey;

        private readonly string $serverHost;
    
        private readonly int $serverPort;
    
        private readonly DirectoryPath $serverLogFileDirectory;

        private readonly FileName $serverLogFileName;

        private readonly string $authSigningKey;
    
        private readonly string $authSigningAlgorithm;
    
        private readonly int $authAccessTokenTTLInMinutes;
    
        private readonly int $authRefreshTokenTTLInMinutes;
    
        private readonly string $authEncryptionKey;

        private readonly string $authFingerprintHashAlgorithm;

        private readonly int $authKeyLength;

        private readonly int $accessControlMaxQueryDepth;

        /**
         * @var array<string>
         */
        private readonly array $accessControlAllowedOrigins;
    
        /**
         * @var array<string>
         */
        private readonly array $accessControlAllowedHeaders;
    
        /**
         * @var array<string>
         */
        private readonly array $accessControlAllowedMethods;
    
        /**
         * @var array<string>
         */
        private readonly array $accessControlExposeHeaders;
    
        private readonly bool $accessControlAllowCredentials;
    
        private readonly int $accessControlApiAccessLimit;
    
        private readonly int $accessControlApiAccessWindowSizeInSeconds;

        private readonly int $accessControlUserContextKeyLength;

        private readonly string $databaseHost;
    
        private readonly int $databasePort;
    
        private readonly string $databaseName;
    
        private readonly string $databaseUser;
    
        private readonly string $databasePassword;

        private readonly DirectoryPaths $doctrineModelsDirectories;
    
        private readonly string $doctrineDBDriver;
    
        /**
         * @var array<string,mixed>
         */
        private readonly array $doctrineConnection;
    
        private readonly bool $doctrineIsDevMode;
    
        private readonly DirectoryPath $doctrineProxiesDirectory;

        private readonly DirectoryPath $watchtowerSchemaFileDirectory;

        private readonly FileName $watchtowerSchemaFileName;
    
        private readonly DirectoryPath $watchtowerCacheDirectory;
    
        private readonly DirectoryPath $watchtowerPluginsDirectory;
    
        private readonly DirectoryPath $watchtowerScalarTypeDefinitionsDirectory;

        private readonly DirectoryPath $emailTemplatesDirectory;
    
        private readonly DirectoryPath $emailTemplatesCacheDirectory;

        private readonly string $symfonyMailerDsn;

        private readonly string $rabbitMQHost;

        private readonly int $rabbitMQPort;
    
        private readonly string $rabbitMQUser;
    
        private readonly string $rabbitMQPassword;

        private readonly string $redisHost;
    
        private readonly int $redisPort;
    
        private readonly string $redisPassword;

        private readonly string $ipAddressParserAttributeName;
    
        private readonly bool $ipAddressParserCheckProxyHeaders;
    
        /**
         * @var array<string>
         */
        private readonly array $ipAddressParserHeadersToInspect;
    
        /**
         * @var array<string>
         */
        private readonly array $ipAddressParserTrustedProxies;

        public function __construct()
        {
            $this->appBaseDirectory = DirectoryPath::{\dirname(__FILE__, 3)}();

            Dotenv::createImmutable(paths: (string) $this->appBaseDirectory)->load();

            $this->appHttpPhrases = require $this->appBaseDirectory.'/config/http_phrases.php';

            $this->appUriSchemes = require $this->appBaseDirectory.'/config/uri_schemes.php';
    
            $this->appEnvironment = (function (): string {
                $appEnvironment =  $_ENV['APP_ENVIRONMENT'] ?? throw new \Exception(
                    message: 'The App environment is not set. Try adding \'APP_ENVIRONMENT\' to the .env file.'
                );
        
                if (!\in_array($appEnvironment, ['production', 'testing', 'development'])) {
                    throw new \Exception('The App environment is invalid. Must be either: production, testing, development.');
                }
        
                return $appEnvironment;
            })();

            $this->appName = $_ENV['APP_NAME'] ?? throw new \Exception(
                message: 'The App name is not set. Try adding \'APP_NAME\' to the .env file.'
            );
    
            $this->appVersion = (function (): string {
                $composerFileDirectory = $this->appBaseDirectory.'/composer.json';
    
                $composerData = \json_decode(
                    \file_get_contents($composerFileDirectory) 
                        ?: throw new \Exception("Error reading file: '$composerFileDirectory'. Kindly ensure it exists.")
                    , true
                );
                
                return $composerData['version'] 
                    ?? throw new \Exception("App version not set. Kindly set it in the composer.json file at '$composerFileDirectory'");
            })();
    
            $this->appDomain = $_ENV['APP_DOMAIN'] ?? throw new \Exception(
                message: 'The App domain is not set. Try adding \'APP_DOMAIN\' to the .env file.'
            );
    
            $this->appEndpoint = $_ENV['APP_ENDPOINT'] ?? throw new \Exception(
                message: 'The App endpoint is not set. Try adding \'APP_ENDPOINT\' to the .env file.'
            );

            $this->appSerializationKey = $_ENV['APP_SERIALIZATION_KEY'] ?? throw new \Exception(
                message: 'The App serialization key is not set. Try add \'APP_SERIALIZATION_KEY\' to the .env file.'
            );

            $this->serverHost = $_ENV['SERVER_HOST'] ?? '0.0.0.0';
    
            $this->serverPort = (function (): int {
                $port =  $_ENV['SERVER_PORT'] ?? '80';
        
                if(!\ctype_digit($port)) {
                    throw new \Exception('The Server port is invalid. Try seting a correct int value.');
                }
        
                return (int) $port;
            })();
    
            $this->serverLogFileDirectory = (function (): DirectoryPath {
                $serverLogFileDirectory = DirectoryPath::{
                    \is_absolute_path($path = $_ENV['SERVER_LOG_FILE_DIRECTORY'] ?? throw new \Exception(
                        message: 'The Server log file directory is not set. Try adding \'SERVER_LOG_FILE_DIRECTORY\' to the .env file.'
                    ))
                    ? $path
                    : $this->appBaseDirectory.'/'.$path
                }();

                if (!\is_dir((string) $serverLogFileDirectory)) {
                    throw new \Exception("The Server log file directory '$serverLogFileDirectory' does not exist. Kindly create it.");
                }

                return $serverLogFileDirectory;
            })();

            $this->serverLogFileName = FileName::{
                $_ENV['SERVER_LOG_FILE_NAME'] ?? throw new \Exception(
                    message: 'The Server log file name is not set. Try adding \'SERVER_LOG_FILE_NAME\' to the .env file.'
                )
            }();
            
            $this->authSigningKey = $_ENV['AUTH_SIGNING_KEY'] ?? throw new \Exception(
                message: 'The Auth signing key is not set. Try adding \'AUTH_SIGNING_KEY\' to the .env file.'
            );
    
            $this->authSigningAlgorithm = $_ENV['AUTH_SIGNING_ALGORITHM'] ?? throw new \Exception(
                message: 'The Auth signing algorithm is not set. Try adding \'AUTH_SIGNING_ALGORITHM\' to the .env file.'
            );
    
            $this->authAccessTokenTTLInMinutes = (function (): int {
                $ttl = $_ENV['AUTH_ACCESS_TOKEN_TTL_MINUTES'] ?? throw new \Exception(
                    message: 'The Auth access token time-to-live is not set. Try adding \'AUTH_ACCESS_TOKEN_TTL_MINUTES\' to the .env file.'
                );
        
                if(!\ctype_digit($ttl)) {
                    throw new \Exception('The Auth access token time-to-live is invalid. Try seting a correct int value.');
                }
        
                return (int) $ttl;
            })();
    
            $this->authRefreshTokenTTLInMinutes = (function (): int {
                $ttl = $_ENV['AUTH_REFRESH_TOKEN_TTL_MINUTES'] ?? throw new \Exception(
                    message: 'The Auth refresh token time-to-live is not set. Try adding \'AUTH_REFRESH_TOKEN_TTL_MINUTES\' to the .env file.'
                );
        
                if(!\ctype_digit($ttl)) {
                    throw new \Exception('The Auth refresh token time-to-live is invalid. Try seting a correct int value.');
                }
        
                return (int) $ttl;
            })();
    
            $this->authEncryptionKey = $_ENV['AUTH_ENCRYPTION_KEY'] ?? throw new \Exception(
                message: 'The Auth encryption key is not set. Try adding \'AUTH_ENCRYPTION_KEY\' to the .env file.'
            );
    
            $this->authFingerprintHashAlgorithm = $_ENV['AUTH_FINGERPRINT_HASH_ALGORITHM'] ?? throw new \Exception(
                message: 'The Auth fingerprint hash algorithm is not set. Try adding \'AUTH_FINGERPRINT_HASH_ALGORITHM\' to the .env file.'
            );

            $this->authKeyLength = (function (): int {
                $ttl = $_ENV['AUTH_KEY_LENGTH'] ?? throw new \Exception(
                    message: 'The Auth key length is not set. Try adding \'AUTH_KEY_LENGTH\' to the .env file.'
                );
        
                if(!\ctype_digit($ttl)) {
                    throw new \Exception('The Auth key length is invalid. Try seting a correct int value.');
                }
        
                return (int) $ttl;
            })();

            $this->accessControlMaxQueryDepth = (function (): int {
                $signUpTokenLimit = $_ENV['ACCESS_CONTROL_MAX_QUERY_DEPTH'] ?? throw new \Exception(
                    message: 'The Access Control Max Query Depth is not set. Try adding \'ACCESS_CONTROL_MAX_QUERY_DEPTH\' to the .env file.'
                );
        
                if(!\ctype_digit($signUpTokenLimit)) {
                    throw new \Exception('The Access Control Max Query Depth is invalid. Try seting a correct int value.');
                }
        
                return (int) $signUpTokenLimit;
            })();

            $this->accessControlAllowedOrigins = \explode(',', $_ENV['ACCESS_CONTROL_ALLOWED_ORIGINS'] ?? throw new \Exception(
                message: 'The Access Control allowed origins is not set. Try adding \'ACCESS_CONTROL_ALLOWED_ORIGINS\' to the .env file.'
            ));
    
            $this->accessControlAllowedHeaders = \explode(',', $_ENV['ACCESS_CONTROL_ALLOWED_HEADERS'] ?? throw new \Exception(
                message: 'The Access Control allowed headers is not set. Try adding \'ACCESS_CONTROL_ALLOWED_HEADERS\' to the .env file.'
            ));
    
            $this->accessControlAllowedMethods = (function (): array {
                $allowedMethods = \explode(',', $_ENV['ACCESS_CONTROL_ALLOWED_METHODS'] ?? throw new \Exception(
                    message: 'The Access Control allowed methods is not set. Try adding \'ACCESS_CONTROL_ALLOWED_METHODS\' to the .env file.'
                ));
        
                $httpMethods = ['GET','HEAD','POST','PUT','DELETE','CONNECT','OPTIONS','TRACE','PATCH'];
        
                if (!\all_in_array($allowedMethods, $httpMethods)) {
                    throw new \Exception('Invalid access controle methods. Must be a subset of '.\implode(',', $httpMethods));
                }
        
                return $allowedMethods;
            })();
    
            $this->accessControlExposeHeaders = (function (): array {
                $exposeHeaders = $_ENV['ACCESS_CONTROL_EXPOSE_HEADERS'];
        
                if (!\is_null($exposeHeaders)) {
                    return \explode(',', $exposeHeaders);
                }
        
                return [];
            })();
    
            $this->accessControlAllowCredentials = $_ENV['ACCESS_CONTROL_ALLOW_CREDENTIALS'] === 'true';
    
            $this->accessControlApiAccessLimit = (function (): int {
                $apiAccessLimit = $_ENV['ACCESS_CONTROL_API_ACCESS_LIMIT'] ?? throw new \Exception(
                    message: 'The Access Control api access limit is not set. Try adding \'ACCESS_CONTROL_API_ACCESS_LIMIT\' to the .env file.'
                );
        
                if(!\ctype_digit($apiAccessLimit)) {
                    throw new \Exception('The Access Control api access limit is invalid. Try seting a correct int value.');
                }
        
                return (int) $apiAccessLimit;
            })();
    
            $this->accessControlApiAccessWindowSizeInSeconds = (function (): int {
                $apiAccessWindowSizeInSeconds = $_ENV['ACCESS_CONTROL_API_ACCESS_WINDOW_SIZE_IN_SECONDS'] ?? throw new \Exception(
                    message: 'The Access Control api access window is not set. Try adding \'ACCESS_CONTROL_API_ACCESS_WINDOW_SIZE_IN_SECONDS\' to the .env file.'
                );
        
                if(!\ctype_digit($apiAccessWindowSizeInSeconds)) {
                    throw new \Exception('The Access Control Api Access Window in Seconds is invalid. Try seting a correct int value.');
                }
        
                return (int) $apiAccessWindowSizeInSeconds;
            })();

            $this->accessControlUserContextKeyLength = (function (): int {
                $apiAccessLimit = $_ENV['ACCESS_CONTROL_USER_CONTEXT_KEY_LENGTH'] ?? throw new \Exception(
                    message: 'The Access Control user context key length is not set. Try adding \'ACCESS_CONTROL_USER_CONTEXT_KEY_LENGTH\' to the .env file.'
                );
        
                if(!\ctype_digit($apiAccessLimit)) {
                    throw new \Exception('The Access Control user context key length is invalid. Try seting a correct int value.');
                }
        
                return (int) $apiAccessLimit;
            })();

            $this->databaseHost = $_ENV['DATABASE_HOST'] ?? throw new \Exception(
                message: 'The Database host is not set. Try adding \'DATABASE_HOST\' to the .env file.'
            );
    
            $this->databasePort = (function (): int {
                $port =  $_ENV['DATABASE_PORT'] ?? throw new \Exception(
                    message: 'The Database port is not set. Try adding \'DATABASE_PORT\' to the .env file.'
                );
        
                if(!\ctype_digit($port)) {
                    throw new \Exception('The Database port is invalid. Try seting a correct int value.');
                }
        
                return (int) $port;
            })();
    
            $this->databaseName = $_ENV['DATABASE_NAME'] ?? throw new \Exception(
                message: 'The Database name is not set. Try adding \'DATABASE_NAME\' to the .env file.'
            );
    
            $this->databaseUser = $_ENV['DATABASE_USER'] ?? throw new \Exception(
                message: 'The Database user is not set. Try adding \'DATABASE_USER\' to the .env file.'
            );
    
            $this->databasePassword = $_ENV['DATABASE_PASSWORD'] ?? throw new \Exception(
                message: 'The Database password is not set. Try adding \'DATABASE_PASSWORD\' to the .env file.'
            );

            $this->doctrineModelsDirectories = (function (): DirectoryPaths {
                $doctrineModelsDirectories = DirectoryPaths::{
                    \implode(
                        ',',
                        \array_map(
                            fn(string $path) => \is_absolute_path($path)
                                ? $path
                                : $this->appBaseDirectory.'/'.$path,
                            \explode(
                                ',',
                                $_ENV['DOCTRINE_MODELS_DIRECTORIES'] ?? throw new \Exception(
                                    message: 'The Doctrine models directories is not set. Try adding \'DOCTRINE_MODELS_DIRECTORIES\' to the .env file.'
                                )
                            )
                        )
                    )
                }();

                foreach (
                    \explode(',', (string) $doctrineModelsDirectories) as $doctrineModelsDirectory
                ) {
                    if (!\is_dir($doctrineModelsDirectory)) {
                        throw new \Exception("The Doctrine models directory '$doctrineModelsDirectory' does not exist. Kindly create it.");
                    }
                }

                return $doctrineModelsDirectories;
            })();
    
            $this->doctrineDBDriver = (function (): string {
                $driver = $_ENV['DOCTRINE_DB_DRIVER'] ?? throw new \Exception(
                    message: 'The Doctrine driver is not set. Try adding \'DOCTRINE_DB_DRIVER\' to the .env file.'
                );
        
                if (!\in_array($driver, ['pdo_mysql', 'pdo_sqlite', 'pdo_pgsql', 'pdo_sqlsrv', 'sqlsrv', 'oci8'])) {
                    throw new \Exception('The Doctrine driver is invalid. Kindly set a proper driver from the Documentation.');
                }
        
                return $driver;
            })();
    
            $this->doctrineConnection = [
                'driver' => $this->doctrineDBDriver,
                'host' => $this->databaseHost,
                'user' => $this->databaseUser,
                'password' => $this->databasePassword,
                'dbname' => $this->databaseName,
                'port' => $this->databasePort,
            ];

            $this->doctrineIsDevMode = $this->appEnvironment === 'development';
    
            $this->doctrineProxiesDirectory = (function (): DirectoryPath {
                $doctrineProxiesDirectory = DirectoryPath::{
                    \is_absolute_path($path = $_ENV['DOCTRINE_PROXIES_DIRECTORY'] ?? throw new \Exception(
                        message: 'The Doctrine proxies directory is not set. Try adding \'DOCTRINE_PROXIES_DIRECTORY\' to the .env file.'
                    ))
                    ? $path
                    : $this->appBaseDirectory.'/'.$path
                }();

                if (!\is_dir((string) $doctrineProxiesDirectory)) {
                    throw new \Exception("The Doctrine proxies directory '$doctrineProxiesDirectory' does not exist. Kindly create it first.");
                }

                return $doctrineProxiesDirectory;
            })();
            
            $this->watchtowerSchemaFileDirectory = (function (): DirectoryPath {
                $watchtowerSchemaFileDirectory = DirectoryPath::{
                    \is_absolute_path($path = $_ENV['WATCHTOWER_SCHEMA_FILE_DIRECTORY'] ?? throw new \Exception(
                        message: 'The Watchtower schema file directory is not set. Try adding \'WATCHTOWER_SCHEMA_FILE_DIRECTORY\' to the .env file.'
                    ))
                    ? $path
                    : $this->appBaseDirectory.'/'.$path
                }();

                if (!\is_dir((string) $watchtowerSchemaFileDirectory)) {
                    throw new \Exception("The Watchtower schema file directory '$watchtowerSchemaFileDirectory' does not exist. Kindly create it first.");
                }

                return $watchtowerSchemaFileDirectory;
            })();
            
            $this->watchtowerSchemaFileName = FileName::{
                $_ENV['WATCHTOWER_SCHEMA_FILE_NAME'] ?? throw new \Exception(
                    message: 'The Watchtower schema file name is not set. Try adding \'WATCHTOWER_SCHEMA_FILE_NAME\' to the .env file.'
                )
            }();
    
            $this->watchtowerCacheDirectory = (function (): DirectoryPath {
                $watchtowerCacheDirectory = DirectoryPath::{
                    \is_absolute_path($path = $_ENV['WATCHTOWER_CACHE_DIRECTORY'] ?? throw new \Exception(
                        message: 'The Watchtower schema cache directory is not set. Try adding \'WATCHTOWER_CACHE_DIRECTORY\' to the .env file.'
                    ))
                    ? $path
                    : $this->appBaseDirectory.'/'.$path
                }();

                if (!\is_dir((string) $watchtowerCacheDirectory)) {
                    throw new \Exception("The Watchtower schema cache directory '$watchtowerCacheDirectory' is not set. Kindly create it first.");
                }

                return $watchtowerCacheDirectory;
            })();
    
            $this->watchtowerPluginsDirectory = (function (): DirectoryPath {
                $watchtowerPluginsDirectory = DirectoryPath::{
                    \is_absolute_path($path = $_ENV['WATCHTOWER_PLUGINS_DIRECTORY'] ?? throw new \Exception(
                        message: 'The Watchtower plugins directory is not set. Try adding \'WATCHTOWER_PLUGINS_DIRECTORY\' to the .env file.'
                    ))
                    ? $path
                    : $this->appBaseDirectory.'/'.$path
                }();

                if (!\is_dir((string) $watchtowerPluginsDirectory)) {
                    throw new \Exception("The Watchtower plugins directory '$watchtowerPluginsDirectory' does not exist. Kindly create it first.");
                }

                return $watchtowerPluginsDirectory;
            })();
    
            $this->watchtowerScalarTypeDefinitionsDirectory = (function (): DirectoryPath {
                $watchtowerScalarTypeDefinitionsDirectory = DirectoryPath::{
                    \is_absolute_path($path = $_ENV['WATCHTOWER_SCALAR_TYPE_DEFINITIONS_DIRECTORY'] ?? throw new \Exception(
                        message: 'The Watchtower scalar type definitions directory is not set. Try adding \'WATCHTOWER_SCALAR_TYPE_DEFINITIONS_DIRECTORY\' to the .env file.'
                    ))
                    ? $path
                    : $this->appBaseDirectory.'/'.$path
                }();

                if (!\is_dir((string) $watchtowerScalarTypeDefinitionsDirectory)) {
                    throw new \Exception("The Watchtower scalar type definitions directory '$watchtowerScalarTypeDefinitionsDirectory' does not exist. Kindly create it first.");
                }

                return $watchtowerScalarTypeDefinitionsDirectory;
            })();

            $this->emailTemplatesDirectory = (function (): DirectoryPath {
                $emailTemplatesDirectory = DirectoryPath::{
                    \is_absolute_path($path = $_ENV['EMAIL_TEMPLATES_DIRECTORY'] ?? throw new \Exception(
                        message: 'The Email templates directory is not set. Try adding \'EMAIL_TEMPLATES_DIRECTORY\' to the .env file.'
                    ))
                    ? $path
                    : $this->appBaseDirectory.'/'.$path
                }();

                if (!\is_dir((string) $emailTemplatesDirectory)) {
                    throw new \Exception("The Email templates directory '$emailTemplatesDirectory' does not exist. Kindly create it first.");
                }

                return $emailTemplatesDirectory;
            })();
    
            $this->emailTemplatesCacheDirectory = (function (): DirectoryPath {
                $emailTemplatesCacheDirectory = DirectoryPath::{
                    \is_absolute_path($path = $_ENV['EMAIL_TEMPLATES_CACHE_DIRECTORY'] ?? throw new \Exception(
                        message: 'The Email templates cache directory is not set. Try adding \'EMAIL_TEMPLATES_CACHE_DIRECTORY\' to the .env file.'
                    ))
                    ? $path
                    : $this->appBaseDirectory.'/'.$path
                }();

                if (!\is_dir((string) $emailTemplatesCacheDirectory)) {
                    throw new \Exception("The Email templates cache directory '$emailTemplatesCacheDirectory' does not exist. Kindly create it first.");
                }

                return $emailTemplatesCacheDirectory;
            })();

            $this->symfonyMailerDsn = $_ENV['SYMFONY_MAILER_DSN'] ?? throw new \Exception(
                message: 'The SymfonyMailer dsn is not set. Try adding \'SYMFONY_MAILER_DSN\' to the .env file.'
            );

            $this->rabbitMQHost = $_ENV['RABBITMQ_HOST'] ?? throw new \Exception(
                message: 'The RabbitMQ host is not set. Try adding \'RABBITMQ_HOST\' to the .env file.'
            );
    
            $this->rabbitMQPort = (function (): int {
                $port =  $_ENV['RABBITMQ_PORT'] ?? throw new \Exception(
                    message: 'The RabbitMQ port is not set. Try adding \'RABBITMQ_PORT\' to the .env file.'
                );
        
                if(!\ctype_digit($port)) {
                    throw new \Exception('The RabbitMQ port is invalid. Try seting a correct int value.');
                }
        
                return (int) $port;
            })();
    
            $this->rabbitMQUser = $_ENV['RABBITMQ_USER'] ?? throw new \Exception(
                message: 'The RabbitMQ user is not set. Try adding \'RABBITMQ_USER\' to the .env file.'
            );
    
            $this->rabbitMQPassword = $_ENV['RABBITMQ_PASSWORD'] ?? throw new \Exception(
                message: 'The RabbitMQ password is not set. Try adding \'RABBITMQ_PASSWORD\' to the .env file.'
            );

            $this->redisHost = $_ENV['REDIS_HOST'] ?? throw new \Exception(
                message: 'The Redis host is not set. Try adding \'REDIS_HOST\' to the .env file.'
            );
    
            $this->redisPort = (function (): int {
                $port =  $_ENV['REDIS_PORT'] ?? throw new \Exception(
                    message: 'The Redis port is not set. Try adding \'REDIS_PORT\' to the .env file.'
                );
        
                if(!\ctype_digit($port)) {
                    throw new \Exception('The Redis port is invalid. Try seting a correct int value.');
                }
        
                return (int) $port;
            })();
    
            $this->redisPassword = $_ENV['REDIS_PASSWORD'] ?? throw new \Exception(
                message: 'The Redis password is not set. Try adding \'REDIS_PASSWORD\' to the .env file.'
            );

            $this->ipAddressParserAttributeName = $_ENV['IP_ADDRESS_ATTRIBUTE_NAME'] ?? throw new \Exception(
                message: 'The ip addres attribute name is not set. Try adding \'IP_ADDRESS_ATTRIBUTE_NAME\' to the .env file.'
            );
    
            $this->ipAddressParserCheckProxyHeaders = $_ENV['IP_ADDRESS_PARSER_CHECK_PROXY_HEADERS'] === 'true';
    
            $this->ipAddressParserHeadersToInspect = \explode(',', $_ENV['IP_ADDRESS_PARSER_HEADERS_TO_INSPECT'] ?? throw new \Exception(
                message: 'The ip addres headers to inspect is not set. Try adding \'IP_ADDRESS_PARSER_HEADERS_TO_INSPECT\' to the .env file.'
            ));
    
            $this->ipAddressParserTrustedProxies = \explode(',', $_ENV['IP_ADDRESS_PARSER_TRUSTED_PROXIES'] ?? throw new \Exception(
                message: 'The ip addres trusted proxies is not set. Try adding \'IP_ADDRESS_PARSER_TRUSTED_PROXIES\' to the .env file.'
            ));
        }

        public function appBaseDirectory(): DirectoryPath
        {
            return $this->appBaseDirectory;
        }
    
        public function appName(): string
        {
            return $this->appName;
        }
    
        public function appVersion(): string
        {
            return $this->appVersion;
        }
    
        public function appEnvironment(): string
        {
            return $this->appEnvironment;
        }
    
        public function appDomain(): string
        {
            return $this->appDomain;
        }
    
        public function appEndpoint(): string
        {
            return $this->appEndpoint;
        }

        public function appSerializationKey(): string
        {
            return $this->appSerializationKey;
        }

        public function appHttpPhrases(): array
        {
            return $this->appHttpPhrases;
        }

        public function appUriSchemes(): array
        {
            return $this->appUriSchemes;
        }
    
        public function serverHost(): string
        {
            return $this->serverHost;
        }
    
        public function serverPort(): int
        {
            return $this->serverPort;
        }
    
        public function serverLogFileDirectory(): DirectoryPath
        {
            return $this->serverLogFileDirectory;
        }

        public function serverLogFileName(): FileName
        {
            return $this->serverLogFileName;
        }
    
        public function authSigningKey(): string
        {
            return $this->authSigningKey;
        }
    
        public function authSigningAlgorithm(): string
        {
            return $this->authSigningAlgorithm;
        }
    
        public function authAccessTokenTTLInMinutes(): int
        {
            return $this->authAccessTokenTTLInMinutes;
        }
    
        public function authRefreshTokenTTLInMinutes(): int
        {
            return $this->authRefreshTokenTTLInMinutes;
        }
    
        public function authEncryptionKey(): string
        {
            return $this->authEncryptionKey;
        }

        public function authFingerprintHashAlgorithm(): string
        {
            return $this->authFingerprintHashAlgorithm;
        }

        public function authKeyLength(): int
        {
            \assert($this->authKeyLength > 0, new \Exception('Invalid authorization key length'));

            return $this->authKeyLength;
        }

        public function accessControlMaxQueryDepth(): int
        {
            return $this->accessControlMaxQueryDepth;
        }

        public function accessControlAllowedOrigins(): array
        {
            return $this->accessControlAllowedOrigins;
        }

        public function accessControlAllowedHeaders(): array
        {
            return $this->accessControlAllowedHeaders;
        }

        public function accessControlAllowedMethods(): array
        {
            return $this->accessControlAllowedMethods;
        }

        public function accessControlExposeHeaders(): array
        {
            return $this->accessControlExposeHeaders;
        }
    
        public function accessControlAllowCredentials(): bool
        {
            return $this->accessControlAllowCredentials;
        }
    
        public function accessControlApiAccessLimit(): int
        {
            return $this->accessControlApiAccessLimit;
        }
    
        public function accessControlApiAccessWindowSizeInSeconds(): int
        {
            return $this->accessControlApiAccessWindowSizeInSeconds;
        }

        public function accessControlUserContextKeyLength(): int
        {
            \assert($this->accessControlUserContextKeyLength > 0, new \Exception('Invalid user context key length'));

            return $this->accessControlUserContextKeyLength;
        }
    
        public function databaseHost(): string
        {
            return $this->databaseHost;
        }
    
        public function databasePort(): int
        {
            return $this->databasePort;
        }
    
        public function databaseName(): string
        {
            return $this->databaseName;
        }
    
        public function databaseUser(): string
        {
            return $this->databaseUser;
        }
    
        public function databasePassword(): string
        {
            return $this->databasePassword;
        }

        public function doctrineModelsDirectories(): DirectoryPaths
        {
            return $this->doctrineModelsDirectories;
        }
    
        public function doctrineDBDriver(): string
        {
            return $this->doctrineDBDriver;
        }

        public function doctrineConnection(): array
        {
            return $this->doctrineConnection;
        }
    
        public function doctrineIsDevMode(): bool
        {
            return $this->doctrineIsDevMode;
        }
    
        public function doctrineProxiesDirectory(): DirectoryPath
        {
            return $this->doctrineProxiesDirectory;
        }

        public function watchtowerSchemaFileDirectory(): DirectoryPath
        {
            return $this->watchtowerSchemaFileDirectory;
        }
    
        public function watchtowerSchemaFileName(): FileName
        {
            return $this->watchtowerSchemaFileName;
        }
    
        public function watchtowerCacheDirectory(): DirectoryPath
        {
            return $this->watchtowerCacheDirectory;
        }
    
        public function watchtowerPluginsDirectory(): DirectoryPath
        {
            return $this->watchtowerPluginsDirectory;
        }
    
        public function watchtowerScalarTypeDefinitionsDirectory(): DirectoryPath
        {
            return $this->watchtowerScalarTypeDefinitionsDirectory;
        }
    
        public function emailTemplatesDirectory(): DirectoryPath
        {
            return $this->emailTemplatesDirectory;
        }
    
        public function emailTemplatesCacheDirectory(): DirectoryPath
        {
            return $this->emailTemplatesCacheDirectory;
        }

        public function symfonyMailerDsn(): string
        {
            return $this->symfonyMailerDsn;
        }
    
        public function rabbitMQHost(): string
        {
            return $this->rabbitMQHost;
        }
    
        public function rabbitMQPort(): int
        {
            return $this->rabbitMQPort;
        }
    
        public function rabbitMQUser(): string
        {
            return $this->rabbitMQUser;
        }
    
        public function rabbitMQPassword(): string
        {
            return $this->rabbitMQPassword;
        }
    
        public function redisHost(): string
        {
            return $this->redisHost;
        }
    
        public function redisPort(): int
        {
            return $this->redisPort;
        }
    
        public function redisPassword(): string
        {
            return $this->redisPassword;
        }
    
        public function ipAddressParserAttributeName(): string
        {
            return $this->ipAddressParserAttributeName;
        }
    
        public function ipAddressParserCheckProxyHeaders(): bool
        {
            return $this->ipAddressParserCheckProxyHeaders;
        }

        public function ipAddressParserHeadersToInspect(): array
        {
            return $this->ipAddressParserHeadersToInspect;
        }

        public function ipAddressParserTrustedProxies(): array
        {
            return $this->ipAddressParserTrustedProxies;
        }
    };

    return $config;
}