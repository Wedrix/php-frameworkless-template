<?php

declare(strict_types=1);

namespace App;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use GraphQL\Error\UserError;
use GraphQL\Executor\ExecutionResult;
use GraphQL\GraphQL;
use GraphQL\Language\AST\DocumentNode;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Schema as GraphQLTypeSchema;
use GraphQL\Validator\Rules\ValidationRule;
use Wedrix\Watchtower\Executor;
use Wedrix\Watchtower\Plugins;
use Wedrix\Watchtower\Resolver;
use Wedrix\Watchtower\Resolver\Node;
use Wedrix\Watchtower\ScalarTypeDefinitions;
use Wedrix\Watchtower\Schema;

final class _WatchtowerExecutor extends Executor
{
    private readonly GraphQLTypeSchema $schema;

    private readonly \Closure $resolver;

    /**
     * @param EntityManagerInterface $entityManager The Doctrine entityManager instance.
     * @param string $schemaFile The schema file.
     * @param string $pluginsDirectory The plugin functions' directory.
     * @param string $scalarTypeDefinitionsDirectory The scalar types' definitions' directory.
     * @param bool $optimize Use the cache for improved perfomance. 
     *      Note: You must run Console::generateCache() to create the cache with the latest changes.
     * @param string $cacheDirectory The directory for storing cache files.
     */
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly string $schemaFile,
        private readonly string $pluginsDirectory,
        private readonly string $scalarTypeDefinitionsDirectory,
        private readonly string $cacheDirectory,
        private readonly bool $optimize
    )
    {
        if (!\is_file($this->schemaFile)) {
            throw new \InvalidConfigurationError("The schema '{$this->schemaFile}' does not exist. Kindly generate it first to proceed.");
        }

        $this->schema = new Schema(
            sourceFile: $this->schemaFile,
            scalarTypeDefinitions: new ScalarTypeDefinitions(
                directory: $this->scalarTypeDefinitionsDirectory,
                cacheDirectory: $this->cacheDirectory,
                optimize: $this->optimize
            ),
            cacheDirectory: $this->cacheDirectory,
            optimize: $this->optimize
        );

        /**
         * @param array<string,mixed> $root
         * @param array<string,mixed> $args
         * @param array<string,mixed> $context
         */
        $this->resolver = function (
            array $root,
            array $args,
            array $context,
            ResolveInfo $info
        ): mixed 
        {
            try {
                $resolver = new Resolver(
                    entityManager: $this->entityManager,
                    plugins: new Plugins(
                        directory: $this->pluginsDirectory,
                        cacheDirectory: $this->cacheDirectory,
                        optimize: $this->optimize
                    )
                );
                
                return $resolver(root: $root, args: $args, context: $context, info: $info);
            }
            catch (NonUniqueResultException) {
                $node = new Node(
                    root: $root,
                    args: $args,
                    context: $context,
                    info: $info
                );
                
                throw new UserError("More than one '{$node->unwrappedType()}' was found for the query although one or none was expected.");
            }
            catch (NoResultException) {
                $node = new Node(
                    root: $root,
                    args: $args,
                    context: $context,
                    info: $info
                );

                throw new UserError("No '{$node->unwrappedType()}' was found for the query although at least one was expected.");
            }
        };
    }

    /**
     * @param array<string,mixed> $rootValue
     * @param array<string,mixed> $contextValue
     * @param array<mixed>|null $variableValues
     * @param array<ValidationRule>|null $validationRules
     */
    public function executeQuery(
        string|DocumentNode $source,
        array $rootValue,
        array $contextValue,
        ?array $variableValues,
        ?string $operationName,
        ?array $validationRules
    ): ExecutionResult 
    {
        return GraphQL::executeQuery(
            schema: $this->schema,
            source: $source,
            rootValue: $rootValue,
            contextValue: $contextValue,
            variableValues: $variableValues,
            operationName: $operationName,
            fieldResolver: $this->resolver,
            validationRules: $validationRules
        );
    }
}