<?php

declare(strict_types=1);

namespace Wedrix\Watchtower\ScalarTypeDefinition\LimitTypeDefinition;

use GraphQL\Error\Error;
use GraphQL\Language\AST\IntValueNode;
use GraphQL\Utils\Utils;

/**
 * Serializes an internal value to include in a response.
 *
 * @param int $value
 * @return int
 */
function serialize($value)
{
    return $value;
}

/**
 * Parses an externally provided value (query variable) to use as an input
 *
 * @param int $value
 * @return int
 * @throws Error
 */
function parseValue($value)
{
    if (($value < 1) || ($value > 100)) {
        throw new Error(
            message: "Cannot represent the following value as Limit: " . Utils::printSafeJson($value)
        );
    }

    return $value;
}

/**
 * Parses an externally provided literal value (hardcoded in GraphQL query) to use as an input.
 * 
 * E.g. 
 * {
 *   limit: 1,
 * }
 *
 * @param \GraphQL\Language\AST\Node $value
 * @param array<string,mixed>|null $variables
 * @return int
 * @throws Error
 */
function parseLiteral($value, ?array $variables = null)
{
    if (!$value instanceof IntValueNode) {
        throw new Error(
            message: "Query error: Can only parse ints got: $value->kind", 
            nodes: $value
        );
    }

    try {
        return parseValue((int) $value->value);
    }
    catch (\Exception $e) {
        throw new Error(
            message: "Not a valid Limit Type",
            nodes: $value,
            previous: $e
        );
    }
}