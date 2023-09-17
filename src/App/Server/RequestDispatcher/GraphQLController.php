<?php

declare(strict_types=1);

namespace App\Server\RequestDispatcher;

use Comet\Request;
use Comet\Response;

use function App\WatchtowerConfig;

function GraphQLController(): Controller
{
    static $controller;
    
    $controller ??= new class() implements Controller {
        /**
         * @param array<string,mixed> $args
         */
        public function __invoke(
            Request $request,
            Response $response,
            array $args = []
        ): Response
        {
            $input = (array) $request->getParsedBody();
    
            $responseBody = $response->getBody() ?? throw new \Exception('Error retrieving response stream.');

            $responseBody->write(
                \is_string(
                    $graphQLResult = \json_encode(
                        WatchtowerExecutor()->executeQuery(
                            source: $input['query'] ?? throw new \Exception('Empty query.'),
                            rootValue: [],
                            contextValue: [
                                'request' => $request,
                                'response' => $response,
                                'args' => $args
                            ],
                            variableValues: $input['variables'] ?? null,
                            operationName: $input['operationName'] ?? null,
                            validationRules: null
                        )
                        ->toArray(
                            debug: WatchtowerConfig()->debugFlag()
                        )
                    )
                ) 
                ? $graphQLResult
                : throw new \Exception('Error evaluating GraphQL result.')
            );
            
            return $response->withHeader('Content-Type', 'application/json; charset=utf-8');
        }
    };

    return $controller;
}