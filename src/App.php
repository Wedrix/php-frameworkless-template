<?php

declare(strict_types=1);

namespace 
{
    use GraphQL\Validator\DocumentValidator;
    use GraphQL\Validator\Rules\DisableIntrospection;
    use GraphQL\Validator\Rules\QueryDepth;
    use Laravel\SerializableClosure\SerializableClosure;

    use function App\Config;
    use function App\Console;
    use function App\DoctrineEntityManager;
    use function App\Server;
    use function App\TwigTemplateEngine;
    use function App\WatchtowerConsole;

    interface App
    {
        public function runConsole(): void;
    
        public function runServer(): void;
    }
    
    function App(): App
    {
        static $app;
        
        $app ??= new class() implements App {
            public function __construct()
            {
                // Install Dependencies
                \system('composer install');
        
                // Require Autoloader
                require \dirname(__DIR__).'/vendor/autoload.php';

                // Uninstall dev dependencies and optimize autoloader for non-dev environments
                if (Config()->appEnvironment() !== 'development') {
                    \system('composer install --no-dev --optimize-autoloader');

                    require \dirname(__DIR__).'/vendor/autoload.php';
                }
        
                // Configure PHP
                \ini_set('zend.assertions', (Config()->appEnvironment() !== 'development') ? 0 : 1);

                \error_reporting(\E_ALL);

                \set_error_handler(
                    function (int $errno, string $errstr, string $errfile, int $errline) {
                        if ($errno == \E_NOTICE || $errno == \E_WARNING) {
                            throw new \LogicError("$errstr in $errfile:$errline");
                        }

                        return false;
                    }
                );

                // Generate Doctrine Proxies for non-dev environments
                if (Config()->appEnvironment() !== 'development') {
                    DoctrineEntityManager()->getProxyFactory()->generateProxyClasses(
                        classes: DoctrineEntityManager()->getMetadataFactory()->getAllMetadata()
                    );
                }

                // Generate Watchtower Cache for non-dev environments
                if (Config()->appEnvironment() !== 'development') {
                    WatchtowerConsole()->generateCache();
                }

                // Generate Email templates Cache for non-dev environments
                if (Config()->appEnvironment() !== 'development') {
                    foreach (
                        new \RegexIterator(
                            iterator: new \RecursiveIteratorIterator(
                                iterator: new \RecursiveDirectoryIterator($emailTemplatesDirectory = (string) Config()->emailTemplatesDirectory())
                            ), 
                            pattern: '/.+\.twig/i',
                            mode: \RegexIterator::MATCH
                        )
                        as $templateFile
                    ) {
                        TwigTemplateEngine()->load(
                            name: \explode($emailTemplatesDirectory, $templateFile->getPathname())[1]
                        );
                    }
                }

                // Configure libraries
                if (Config()->appEnvironment() !== 'development') {
                    DocumentValidator::addRule(
                        rule: new QueryDepth(
                            maxQueryDepth: Config()->accessControlMaxQueryDepth()
                        )
                    );
                
                    DocumentValidator::addRule(
                        rule: new DisableIntrospection()
                    );
                    
                    SerializableClosure::setSecretKey(Config()->appSerializationKey());
                }

                echo "\e[H\e[J"; // Clear terminal
            }

            public function runConsole(): void
            {
                Console()->run();
            }
        
            public function runServer(): void
            {
                Server()->run();
            }
        };
    
        return $app;
    }
}

namespace App 
{
    //TODO: Determinations go here ...
}