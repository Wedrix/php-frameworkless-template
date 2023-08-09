<?php

declare(strict_types=1);

namespace 
{
    use GraphQL\Validator\DocumentValidator;
    use GraphQL\Validator\Rules\DisableIntrospection;
    use GraphQL\Validator\Rules\QueryDepth;
    use Laravel\SerializableClosure\SerializableClosure;

    use function App\AppConfig;
    use function App\Console;
    use function App\Server;
    use function App\WatchtowerConfig;

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
                if (WatchtowerConfig()->enableSecurityRules()) {
                    DocumentValidator::addRule(
                        rule: new QueryDepth(
                            maxQueryDepth: 3
                        )
                    );
                
                    DocumentValidator::addRule(
                        rule: new DisableIntrospection()
                    );
                }

                SerializableClosure::setSecretKey(AppConfig()->serializationKey());
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