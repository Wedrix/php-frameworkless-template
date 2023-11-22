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
    use function App\DataStore;
    use function App\Server;
    use function App\TwigTemplateEngine;

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
                \system('composer install');
        
                require \dirname(__DIR__).'/vendor/autoload.php';

                // Uninstall dev dependencies and optimize autoloader for non-dev environments
                if (Config()->appEnvironment() !== 'development') {
                    \system('composer install --no-dev --optimize-autoloader');

                    require \dirname(__DIR__).'/vendor/autoload.php';
                }
        
                // Configure PHP
                if (Config()->appEnvironment() !== 'development') {
                    \ini_set('zend.assertions', 0);
                }
                else {
                    \ini_set('zend.assertions', 1);
                }

                // Generate Doctrine Proxies for non-dev environments
                if (Config()->appEnvironment() !== 'development') {
                    DataStore()->getProxyFactory()->generateProxyClasses(
                        classes: DataStore()->getMetadataFactory()->getAllMetadata()
                    );
                }

                // Generate Email templates Cache for non-dev environments
                if (Config()->appEnvironment() !== 'development') {
                    foreach (
                        new \RegexIterator(
                            iterator: new \RecursiveIteratorIterator(
                                iterator: new \RecursiveDirectoryIterator((string) Config()->emailTemplatesDirectory())
                            ), 
                            pattern: '/.+\.twig/i',
                            mode: \RegexIterator::MATCH
                        )
                        as $templateFile
                    ) {
                        $emailTemplatesDirectory = (string) Config()->emailTemplatesDirectory();

                        assert(!empty($emailTemplatesDirectory), new \Exception('Empty emailTemplatesDirectory!'));

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
    //TODO: ... determinations go here
}