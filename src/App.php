<?php

declare(strict_types=1);

namespace 
{
    use GraphQL\Validator\DocumentValidator;
    use GraphQL\Validator\Rules\DisableIntrospection;
    use GraphQL\Validator\Rules\QueryDepth;
    use Laravel\SerializableClosure\SerializableClosure;

    use function App\AccessControlConfig;
    use function App\AppConfig;
    use function App\Console;
    use function App\Server;

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
                if (AppConfig()->environment() === 'production') {
                    DocumentValidator::addRule(
                        rule: new QueryDepth(
                            maxQueryDepth: AccessControlConfig()->maxQueryDepth()
                        )
                    );
                
                    DocumentValidator::addRule(
                        rule: new DisableIntrospection()
                    );
                    
                    SerializableClosure::setSecretKey(AppConfig()->serializationKey());
                }
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