<?php

declare(strict_types=1);

namespace Wedrix\Watchtower\ScalarTypeDefinition\DateTimeTypeDefinition;

use GraphQL\Error\Error;
use GraphQL\Language\AST\StringValueNode;
use GraphQL\Utils\Utils;

/**
 * Serializes an internal value to include in a response.
 *
 * @param \DateTimeImmutable $value
 * @return string
 */
function serialize($value)
{
    return $value->format(\DateTimeImmutable::ATOM);
}

/**
 * Parses an externally provided value (query variable) to use as an input
 *
 * @param string $value
 * @return \DateTimeImmutable
 */
function parseValue($value)
{
    return \date_create_immutable($value) 
        ?: throw new Error(
            message: "Cannot represent the following value as DateTime: " . Utils::printSafeJson($value)
        );
}

/**
 * Parses an externally provided literal value (hardcoded in GraphQL query) to use as an input.
 * 
 * E.g. 
 * {
 *   user(createdAt: "2021-01-24T05:16:41+00:00") 
 * }
 *
 * @param \GraphQL\Language\AST\Node $value
 * @param array<string,mixed>|null $variables
 * @return \DateTimeImmutable
 * @throws Error
 */
function parseLiteral($value, ?array $variables = null)
{
    if (!$value instanceof StringValueNode) {
        throw new Error(
            message: "Query error: Can only parse strings got: $value->kind",
            nodes: $value
        );
    }

    try {
        return parseValue($value->value);
    }
    catch (\Exception $e) {
        throw new Error(
            message: "Not a valid DateTime Type", 
            nodes: $value,
            previous: $e
        );
    }
}