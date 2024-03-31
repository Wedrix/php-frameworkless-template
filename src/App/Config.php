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

    public function appEnvironment(): string;

    public function appName(): string;

    public function appVersion(): string;

    public function appDomain(): string;

    public function appEndpoint(): string;

    public function appSerializationKey(): string;

    public function serverHost(): string;

    public function serverPort(): int;

    public function serverLogFilesDirectory(): DirectoryPath;
    
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
    
        private readonly DirectoryPath $serverLogFilesDirectory;

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
            try {
                if (!\in_array(\PHP_OS_FAMILY, ['Linux','Darwin','Windows'])) {
                    throw new \InvalidConfigurationError(
                        'This application does not currently run with your operating system. 
                        Kindly consider running it with either Linux, MacOS, or Windows (preferably using the Windows Subsystem for Linux).'
                    );
                }
    
                $this->appBaseDirectory = $appBaseDirectory = DirectoryPath::{\dirname(__FILE__, 3)}();
    
                Dotenv::createImmutable(paths: (string) $appBaseDirectory)->load();
    
                $this->appHttpPhrases = require (string) FilePath::{$appBaseDirectory.'/config/http_phrases.php'}();
    
                $this->appUriSchemes = require (string) FilePath::{$appBaseDirectory.'/config/uri_schemes.php'}();
        
                $this->appEnvironment = $appEnvironment = (static function(): string {
                    $appEnvironment =  $_ENV['APP_ENVIRONMENT'] ?? throw new \InvalidConfigurationError(
                        message: 'The App Environment is not set. Try adding \'APP_ENVIRONMENT\' to the .env file.'
                    );
            
                    if (!\in_array($appEnvironment, ['production', 'testing', 'development'])) {
                        throw new \InvalidDataException('The App Environment is invalid. Must be either: production, testing, development.');
                    }
            
                    return $appEnvironment;
                })();
    
                $this->appName = $_ENV['APP_NAME'] ?? throw new \InvalidConfigurationError(
                    message: 'The App Name is not set. Try adding \'APP_NAME\' to the .env file.'
                );
        
                $this->appVersion = (static function() use($appBaseDirectory): string {
                    $composerFileDirectory = FilePath::{$appBaseDirectory.'/composer.json'}();
        
                    $composerData = \json_decode(
                        \is_string($fileContents = \file_get_contents((string) $composerFileDirectory)) 
                            ? $fileContents
                            : throw new \IOException("Error reading file '$composerFileDirectory'.")
                        , true
                    );
                    
                    return $composerData['version'] 
                        ?? throw new \InvalidConfigurationError("App version is not set. Kindly set it in the composer.json file at '$composerFileDirectory'");
                })();
        
                $this->appDomain = $_ENV['APP_DOMAIN'] ?? throw new \InvalidConfigurationError(
                    message: 'The App Domain is not set. Try adding \'APP_DOMAIN\' to the .env file.'
                );
        
                $this->appEndpoint = $_ENV['APP_ENDPOINT'] ?? throw new \InvalidConfigurationError(
                    message: 'The App Endpoint is not set. Try adding \'APP_ENDPOINT\' to the .env file.'
                );
    
                $this->appSerializationKey = $_ENV['APP_SERIALIZATION_KEY'] ?? throw new \InvalidConfigurationError(
                    message: 'The App Serialization Key is not set. Try add \'APP_SERIALIZATION_KEY\' to the .env file.'
                );
    
                $this->serverHost = $_ENV['SERVER_HOST'] ?? '0.0.0.0';
        
                $this->serverPort = (static function(): int {
                    $port =  $_ENV['SERVER_PORT'] ?? '80';
            
                    if(!\ctype_digit($port)) {
                        throw new \InvalidDataException('The Server Port is invalid. Try setting a correct int value.');
                    }
            
                    return (int) $port;
                })();
        
                $this->serverLogFilesDirectory = (static function() use($appBaseDirectory): DirectoryPath {
                    $serverLogFilesDirectory = DirectoryPath::{
                        \is_absolute_path($path = $_ENV['SERVER_LOG_FILES_DIRECTORY'] ?? throw new \InvalidConfigurationError(
                            message: 'The Server Log Files Directory is not set. Try adding \'SERVER_LOG_FILES_DIRECTORY\' to the .env file.'
                        ))
                        ? $path
                        : $appBaseDirectory.'/'.$path
                    }();
    
                    if (!\is_dir((string) $serverLogFilesDirectory)) {
                        throw new \InvalidConfigurationError("The Server Log Files Directory '$serverLogFilesDirectory' does not exist. Kindly create it.");
                    }
    
                    return $serverLogFilesDirectory;
                })();
                
                $this->authSigningKey = $_ENV['AUTH_SIGNING_KEY'] ?? throw new \InvalidConfigurationError(
                    message: 'The Auth Signing Key is not set. Try adding \'AUTH_SIGNING_KEY\' to the .env file.'
                );
        
                $this->authSigningAlgorithm = $_ENV['AUTH_SIGNING_ALGORITHM'] ?? throw new \InvalidConfigurationError(
                    message: 'The Auth Signing Algorithm is not set. Try adding \'AUTH_SIGNING_ALGORITHM\' to the .env file.'
                );
        
                $this->authAccessTokenTTLInMinutes = (static function(): int {
                    $authAccessTokenTTLInMinutes = $_ENV['AUTH_ACCESS_TOKEN_TTL_MINUTES'] ?? throw new \InvalidConfigurationError(
                        message: 'The Auth Access Token Time To Live in Minutes is not set. Try adding \'AUTH_ACCESS_TOKEN_TTL_MINUTES\' to the .env file.'
                    );
            
                    if (!\ctype_digit($authAccessTokenTTLInMinutes)) {
                        throw new \InvalidDataException('The Auth Access Token Time To Live in Minutes is invalid. Try setting a correct int value.');
                    }
            
                    return (int) $authAccessTokenTTLInMinutes;
                })();
        
                $this->authRefreshTokenTTLInMinutes = (static function(): int {
                    $authRefreshTokenTTLInMinutes = $_ENV['AUTH_REFRESH_TOKEN_TTL_MINUTES'] ?? throw new \InvalidConfigurationError(
                        message: 'The Auth Refresh Token Time To Live in Minutes is not set. Try adding \'AUTH_REFRESH_TOKEN_TTL_MINUTES\' to the .env file.'
                    );
            
                    if (!\ctype_digit($authRefreshTokenTTLInMinutes)) {
                        throw new \InvalidDataException('The Auth Refresh Token Time To Live in Minutes is invalid. Try setting a correct int value.');
                    }
            
                    return (int) $authRefreshTokenTTLInMinutes;
                })();
        
                $this->authEncryptionKey = $_ENV['AUTH_ENCRYPTION_KEY'] ?? throw new \InvalidConfigurationError(
                    message: 'The Auth Encryption Key is not set. Try adding \'AUTH_ENCRYPTION_KEY\' to the .env file.'
                );
        
                $this->authFingerprintHashAlgorithm = $_ENV['AUTH_FINGERPRINT_HASH_ALGORITHM'] ?? throw new \InvalidConfigurationError(
                    message: 'The Auth Fingerprint Hash Algorithm is not set. Try adding \'AUTH_FINGERPRINT_HASH_ALGORITHM\' to the .env file.'
                );
    
                $this->authKeyLength = (static function(): int {
                    $authKeyLength = $_ENV['AUTH_KEY_LENGTH'] ?? throw new \InvalidConfigurationError(
                        message: 'The Auth Key Length is not set. Try adding \'AUTH_KEY_LENGTH\' to the .env file.'
                    );
            
                    if (!\ctype_digit($authKeyLength)) {
                        throw new \InvalidDataException('The Auth Key Length is invalid. Try setting a correct int value.');
                    }
    
                    if ($authKeyLength < 1) {
                        throw new \InvalidDataException('The Auth Key Length is invalid. Try setting a value in the range value > 0.');
                    }
            
                    return (int) $authKeyLength;
                })();
    
                $this->accessControlMaxQueryDepth = (static function(): int {
                    $accessControlMaxQueryDepth = $_ENV['ACCESS_CONTROL_MAX_QUERY_DEPTH'] ?? throw new \InvalidConfigurationError(
                        message: 'The Access Control Max Query Depth is not set. Try adding \'ACCESS_CONTROL_MAX_QUERY_DEPTH\' to the .env file.'
                    );
            
                    if (!\ctype_digit($accessControlMaxQueryDepth)) {
                        throw new \InvalidDataException('The Access Control Max Query Depth is invalid. Try setting a correct int value.');
                    }
            
                    return (int) $accessControlMaxQueryDepth;
                })();
    
                $this->accessControlAllowedOrigins = \explode(',', $_ENV['ACCESS_CONTROL_ALLOWED_ORIGINS'] ?? throw new \InvalidConfigurationError(
                    message: 'The Access Control Allowed Origins is not set. Try adding \'ACCESS_CONTROL_ALLOWED_ORIGINS\' to the .env file.'
                ));
        
                $this->accessControlAllowedHeaders = \explode(',', $_ENV['ACCESS_CONTROL_ALLOWED_HEADERS'] ?? throw new \InvalidConfigurationError(
                    message: 'The Access Control Allowed Headers is not set. Try adding \'ACCESS_CONTROL_ALLOWED_HEADERS\' to the .env file.'
                ));
        
                $this->accessControlAllowedMethods = (static function(): array {
                    $allowedMethods = \explode(',', $_ENV['ACCESS_CONTROL_ALLOWED_METHODS'] ?? throw new \InvalidConfigurationError(
                        message: 'The Access Control Allowed Methods is not set. Try adding \'ACCESS_CONTROL_ALLOWED_METHODS\' to the .env file.'
                    ));
            
                    $httpMethods = ['GET','HEAD','POST','PUT','DELETE','CONNECT','OPTIONS','TRACE','PATCH'];
            
                    if (!\all_in_array($allowedMethods, $httpMethods)) {
                        throw new \InvalidConfigurationError('Invalid Access Control Allowed Methods. Must be a subset of '.\implode(',', $httpMethods));
                    }
            
                    return $allowedMethods;
                })();
        
                $this->accessControlExposeHeaders = (static function(): array {
                    $exposeHeaders = $_ENV['ACCESS_CONTROL_EXPOSE_HEADERS'];
            
                    if (!\is_null($exposeHeaders)) {
                        return \explode(',', $exposeHeaders);
                    }
            
                    return [];
                })();
        
                $this->accessControlAllowCredentials = $_ENV['ACCESS_CONTROL_ALLOW_CREDENTIALS'] === 'true';
        
                $this->accessControlApiAccessLimit = (static function(): int {
                    $apiAccessLimit = $_ENV['ACCESS_CONTROL_API_ACCESS_LIMIT'] ?? throw new \InvalidConfigurationError(
                        message: 'The Access Control Api Access Limit is not set. Try adding \'ACCESS_CONTROL_API_ACCESS_LIMIT\' to the .env file.'
                    );
            
                    if (!\ctype_digit($apiAccessLimit)) {
                        throw new \InvalidDataException('The Access Control Api Access Limit is invalid. Try setting a correct int value.');
                    }
            
                    return (int) $apiAccessLimit;
                })();
        
                $this->accessControlApiAccessWindowSizeInSeconds = (static function(): int {
                    $apiAccessWindowSizeInSeconds = $_ENV['ACCESS_CONTROL_API_ACCESS_WINDOW_SIZE_IN_SECONDS'] ?? throw new \InvalidConfigurationError(
                        message: 'The Access Control Api Access Window in Seconds is not set. Try adding \'ACCESS_CONTROL_API_ACCESS_WINDOW_SIZE_IN_SECONDS\' to the .env file.'
                    );
            
                    if (!\ctype_digit($apiAccessWindowSizeInSeconds)) {
                        throw new \InvalidDataException('The Access Control Api Access Window in Seconds is invalid. Try setting a correct int value.');
                    }
            
                    return (int) $apiAccessWindowSizeInSeconds;
                })();
    
                $this->accessControlUserContextKeyLength = (static function(): int {
                    $userContextKeyLength = $_ENV['ACCESS_CONTROL_USER_CONTEXT_KEY_LENGTH'] ?? throw new \InvalidConfigurationError(
                        message: 'The Access Control User Context Key Length is not set. Try adding \'ACCESS_CONTROL_USER_CONTEXT_KEY_LENGTH\' to the .env file.'
                    );
            
                    if (!\ctype_digit($userContextKeyLength)) {
                        throw new \InvalidDataException('The Access Control User Context Key Length is invalid. Try setting a correct int value.');
                    }
    
                    if ($userContextKeyLength < 1) {
                        throw new \InvalidDataException('The Access Control User Context Key Length is invalid. Try setting a value in the range > 0.');
                    }
            
                    return (int) $userContextKeyLength;
                })();
    
                $this->databaseHost = $databaseHost = $_ENV['DATABASE_HOST'] ?? throw new \InvalidConfigurationError(
                    message: 'The Database Host is not set. Try adding \'DATABASE_HOST\' to the .env file.'
                );
        
                $this->databasePort = $databasePort = (static function(): int {
                    $port =  $_ENV['DATABASE_PORT'] ?? throw new \InvalidConfigurationError(
                        message: 'The Database Port is not set. Try adding \'DATABASE_PORT\' to the .env file.'
                    );
            
                    if (!\ctype_digit($port)) {
                        throw new \InvalidDataException('The Database Port is invalid. Try setting a correct int value.');
                    }
            
                    return (int) $port;
                })();
        
                $this->databaseName = $databaseName = $_ENV['DATABASE_NAME'] ?? throw new \InvalidConfigurationError(
                    message: 'The Database Name is not set. Try adding \'DATABASE_NAME\' to the .env file.'
                );
        
                $this->databaseUser = $databaseUser = $_ENV['DATABASE_USER'] ?? throw new \InvalidConfigurationError(
                    message: 'The Database User is not set. Try adding \'DATABASE_USER\' to the .env file.'
                );
        
                $this->databasePassword = $databasePassword = $_ENV['DATABASE_PASSWORD'] ?? throw new \InvalidConfigurationError(
                    message: 'The Database Password is not set. Try adding \'DATABASE_PASSWORD\' to the .env file.'
                );
    
                $this->doctrineModelsDirectories = (static function() use($appBaseDirectory): DirectoryPaths {
                    $doctrineModelsDirectories = DirectoryPaths::{
                        \implode(
                            ',',
                            \array_map(
                                static fn(string $path) => \is_absolute_path($path)
                                    ? $path
                                    : $appBaseDirectory.'/'.$path,
                                \explode(
                                    ',',
                                    $_ENV['DOCTRINE_MODELS_DIRECTORIES'] ?? throw new \InvalidConfigurationError(
                                        message: 'The Doctrine Models Directories is not set. Try adding \'DOCTRINE_MODELS_DIRECTORIES\' to the .env file.'
                                    )
                                )
                            )
                        )
                    }();
    
                    foreach (
                        \explode(',', (string) $doctrineModelsDirectories) as $doctrineModelsDirectory
                    ) {
                        if (!\is_dir($doctrineModelsDirectory)) {
                            throw new \InvalidConfigurationError("The Doctrine Models Directory '$doctrineModelsDirectory' does not exist. Kindly create it.");
                        }
                    }
    
                    return $doctrineModelsDirectories;
                })();
        
                $this->doctrineDBDriver = $doctrineDBDriver = (static function(): string {
                    $driver = $_ENV['DOCTRINE_DB_DRIVER'] ?? throw new \InvalidConfigurationError(
                        message: 'The Doctrine Driver is not set. Try adding \'DOCTRINE_DB_DRIVER\' to the .env file.'
                    );
            
                    if (!\in_array($driver, ['pdo_mysql', 'pdo_sqlite', 'pdo_pgsql', 'pdo_sqlsrv', 'sqlsrv', 'oci8'])) {
                        throw new \InvalidDataException('The Doctrine Driver is invalid. Kindly set a proper driver from the Documentation.');
                    }
            
                    return $driver;
                })();
        
                $this->doctrineConnection = [
                    'driver' => $doctrineDBDriver,
                    'host' => $databaseHost,
                    'user' => $databaseUser,
                    'password' => $databasePassword,
                    'dbname' => $databaseName,
                    'port' => $databasePort,
                ];
    
                $this->doctrineIsDevMode = ($appEnvironment === 'development');
        
                $this->doctrineProxiesDirectory = (static function() use($appBaseDirectory): DirectoryPath {
                    $doctrineProxiesDirectory = DirectoryPath::{
                        \is_absolute_path($path = $_ENV['DOCTRINE_PROXIES_DIRECTORY'] ?? throw new \InvalidConfigurationError(
                            message: 'The Doctrine Proxies Directory is not set. Try adding \'DOCTRINE_PROXIES_DIRECTORY\' to the .env file.'
                        ))
                        ? $path
                        : $appBaseDirectory.'/'.$path
                    }();
    
                    if (!\is_dir((string) $doctrineProxiesDirectory)) {
                        throw new \IOException("The Doctrine Proxies Directory '$doctrineProxiesDirectory' does not exist. Kindly create it first.");
                    }
    
                    return $doctrineProxiesDirectory;
                })();
                
                $this->watchtowerSchemaFileDirectory = (static function() use($appBaseDirectory): DirectoryPath {
                    $watchtowerSchemaFileDirectory = DirectoryPath::{
                        \is_absolute_path($path = $_ENV['WATCHTOWER_SCHEMA_FILE_DIRECTORY'] ?? throw new \InvalidConfigurationError(
                            message: 'The Watchtower Schema File Directory is not set. Try adding \'WATCHTOWER_SCHEMA_FILE_DIRECTORY\' to the .env file.'
                        ))
                        ? $path
                        : $appBaseDirectory.'/'.$path
                    }();
    
                    if (!\is_dir((string) $watchtowerSchemaFileDirectory)) {
                        throw new \InvalidConfigurationError("The Watchtower Schema File Directory '$watchtowerSchemaFileDirectory' does not exist. Kindly create it first.");
                    }
    
                    return $watchtowerSchemaFileDirectory;
                })();
                
                $this->watchtowerSchemaFileName = FileName::{
                    $_ENV['WATCHTOWER_SCHEMA_FILE_NAME'] ?? throw new \InvalidConfigurationError(
                        message: 'The Watchtower Schema File Name is not set. Try adding \'WATCHTOWER_SCHEMA_FILE_NAME\' to the .env file.'
                    )
                }();
        
                $this->watchtowerCacheDirectory = (static function() use($appBaseDirectory): DirectoryPath {
                    $watchtowerCacheDirectory = DirectoryPath::{
                        \is_absolute_path($path = $_ENV['WATCHTOWER_CACHE_DIRECTORY'] ?? throw new \InvalidConfigurationError(
                            message: 'The Watchtower Schema Cache Directory is not set. Try adding \'WATCHTOWER_CACHE_DIRECTORY\' to the .env file.'
                        ))
                        ? $path
                        : $appBaseDirectory.'/'.$path
                    }();
    
                    if (!\is_dir((string) $watchtowerCacheDirectory)) {
                        throw new \InvalidConfigurationError("The Watchtower Schema Cache Directory '$watchtowerCacheDirectory' is not set. Kindly create it first.");
                    }
    
                    return $watchtowerCacheDirectory;
                })();
        
                $this->watchtowerPluginsDirectory = (static function() use($appBaseDirectory): DirectoryPath {
                    $watchtowerPluginsDirectory = DirectoryPath::{
                        \is_absolute_path($path = $_ENV['WATCHTOWER_PLUGINS_DIRECTORY'] ?? throw new \InvalidConfigurationError(
                            message: 'The Watchtower Plugins Directory is not set. Try adding \'WATCHTOWER_PLUGINS_DIRECTORY\' to the .env file.'
                        ))
                        ? $path
                        : $appBaseDirectory.'/'.$path
                    }();
    
                    if (!\is_dir((string) $watchtowerPluginsDirectory)) {
                        throw new \InvalidConfigurationError("The Watchtower Plugins Directory '$watchtowerPluginsDirectory' does not exist. Kindly create it first.");
                    }
    
                    return $watchtowerPluginsDirectory;
                })();
        
                $this->watchtowerScalarTypeDefinitionsDirectory = (static function() use($appBaseDirectory): DirectoryPath {
                    $watchtowerScalarTypeDefinitionsDirectory = DirectoryPath::{
                        \is_absolute_path($path = $_ENV['WATCHTOWER_SCALAR_TYPE_DEFINITIONS_DIRECTORY'] ?? throw new \InvalidConfigurationError(
                            message: 'The Watchtower Scalar Type Definitions Directory is not set. Try adding \'WATCHTOWER_SCALAR_TYPE_DEFINITIONS_DIRECTORY\' to the .env file.'
                        ))
                        ? $path
                        : $appBaseDirectory.'/'.$path
                    }();
    
                    if (!\is_dir((string) $watchtowerScalarTypeDefinitionsDirectory)) {
                        throw new \InvalidConfigurationError("The Watchtower Scalar Type Definitions Directory '$watchtowerScalarTypeDefinitionsDirectory' does not exist. Kindly create it first.");
                    }
    
                    return $watchtowerScalarTypeDefinitionsDirectory;
                })();
    
                $this->emailTemplatesDirectory = (static function() use($appBaseDirectory): DirectoryPath {
                    $emailTemplatesDirectory = DirectoryPath::{
                        \is_absolute_path($path = $_ENV['EMAIL_TEMPLATES_DIRECTORY'] ?? throw new \InvalidConfigurationError(
                            message: 'The Email Templates Directory is not set. Try adding \'EMAIL_TEMPLATES_DIRECTORY\' to the .env file.'
                        ))
                        ? $path
                        : $appBaseDirectory.'/'.$path
                    }();
    
                    if (!\is_dir((string) $emailTemplatesDirectory)) {
                        throw new \InvalidConfigurationError("The Email Templates Directory '$emailTemplatesDirectory' does not exist. Kindly create it first.");
                    }
    
                    return $emailTemplatesDirectory;
                })();
        
                $this->emailTemplatesCacheDirectory = (static function() use($appBaseDirectory): DirectoryPath {
                    $emailTemplatesCacheDirectory = DirectoryPath::{
                        \is_absolute_path($path = $_ENV['EMAIL_TEMPLATES_CACHE_DIRECTORY'] ?? throw new \InvalidConfigurationError(
                            message: 'The Email Templates Cache Directory is not set. Try adding \'EMAIL_TEMPLATES_CACHE_DIRECTORY\' to the .env file.'
                        ))
                        ? $path
                        : $appBaseDirectory.'/'.$path
                    }();
    
                    if (!\is_dir((string) $emailTemplatesCacheDirectory)) {
                        throw new \InvalidConfigurationError("The Email Templates Cache Directory '$emailTemplatesCacheDirectory' does not exist. Kindly create it first.");
                    }
    
                    return $emailTemplatesCacheDirectory;
                })();
    
                $this->symfonyMailerDsn = $_ENV['SYMFONY_MAILER_DSN'] ?? throw new \InvalidConfigurationError(
                    message: 'The SymfonyMailer Dsn is not set. Try adding \'SYMFONY_MAILER_DSN\' to the .env file.'
                );
    
                $this->rabbitMQHost = $_ENV['RABBITMQ_HOST'] ?? throw new \InvalidConfigurationError(
                    message: 'The RabbitMQ Host is not set. Try adding \'RABBITMQ_HOST\' to the .env file.'
                );
        
                $this->rabbitMQPort = (static function(): int {
                    $port =  $_ENV['RABBITMQ_PORT'] ?? throw new \InvalidConfigurationError(
                        message: 'The RabbitMQ Port is not set. Try adding \'RABBITMQ_PORT\' to the .env file.'
                    );
            
                    if (!\ctype_digit($port)) {
                        throw new \InvalidDataException('The RabbitMQ Port is invalid. Try setting a correct int value.');
                    }
            
                    return (int) $port;
                })();
        
                $this->rabbitMQUser = $_ENV['RABBITMQ_USER'] ?? throw new \InvalidConfigurationError(
                    message: 'The RabbitMQ User is not set. Try adding \'RABBITMQ_USER\' to the .env file.'
                );
        
                $this->rabbitMQPassword = $_ENV['RABBITMQ_PASSWORD'] ?? throw new \InvalidConfigurationError(
                    message: 'The RabbitMQ Password is not set. Try adding \'RABBITMQ_PASSWORD\' to the .env file.'
                );
    
                $this->redisHost = $_ENV['REDIS_HOST'] ?? throw new \InvalidConfigurationError(
                    message: 'The Redis Host is not set. Try adding \'REDIS_HOST\' to the .env file.'
                );
        
                $this->redisPort = (static function(): int {
                    $port =  $_ENV['REDIS_PORT'] ?? throw new \InvalidConfigurationError(
                        message: 'The Redis Port is not set. Try adding \'REDIS_PORT\' to the .env file.'
                    );
            
                    if (!\ctype_digit($port)) {
                        throw new \InvalidDataException('The Redis Port is invalid. Try setting a correct int value.');
                    }
            
                    return (int) $port;
                })();
        
                $this->redisPassword = $_ENV['REDIS_PASSWORD'] ?? throw new \InvalidConfigurationError(
                    message: 'The Redis Password is not set. Try adding \'REDIS_PASSWORD\' to the .env file.'
                );
        
                $this->ipAddressParserCheckProxyHeaders = $_ENV['IP_ADDRESS_PARSER_CHECK_PROXY_HEADERS'] === 'true';
        
                $this->ipAddressParserHeadersToInspect = \explode(',', $_ENV['IP_ADDRESS_PARSER_HEADERS_TO_INSPECT'] ?? throw new \InvalidConfigurationError(
                    message: 'The Ip Address Parser Headers to Inspect is not set. Try adding \'IP_ADDRESS_PARSER_HEADERS_TO_INSPECT\' to the .env file.'
                ));
        
                $this->ipAddressParserTrustedProxies = \explode(',', $_ENV['IP_ADDRESS_PARSER_TRUSTED_PROXIES'] ?? throw new \InvalidConfigurationError(
                    message: 'The Ip Address Parser Trusted Proxies is not set. Try adding \'IP_ADDRESS_PARSER_TRUSTED_PROXIES\' to the .env file.'
                ));
    
                if ($this->ipAddressParserCheckProxyHeaders && empty($this->ipAddressParserTrustedProxies)) {
                    throw new \InvalidConfigurationError(
                        'Use of the forward headers requires an array for trusted proxies. 
                        Try passing a list of trusted proxies to \'IP_ADDRESS_PARSER_TRUSTED_PROXIES\' in the .env file'
                    );
                }
            }
            catch (\InvalidDataException $e) {
                throw new \InvalidConfigurationError(
                    message: $e->getMessage(),
                    previous: $e
                );
            }
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
    
        public function serverLogFilesDirectory(): DirectoryPath
        {
            return $this->serverLogFilesDirectory;
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