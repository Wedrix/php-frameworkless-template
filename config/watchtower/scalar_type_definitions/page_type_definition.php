<?php

declare(strict_types=1);

namespace Wedrix\Watchtower\ScalarTypeDefinition\PageTypeDefinition;

use GraphQL\Language\AST\IntValueNode;

/**
 * Serializes an internal value to include in a response.
 */
function serialize(
    int $value
): int
{
    return $value;
}

/**
 * Parses an externally provided value (query variable) to use as an input
 */
function parseValue(
    int $value
): int
{
    if (($value < 1)) {
        throw new \Exception('Invalid Page value!');
    }

    return $value;
}

/**
 * Parses an externally provided literal value (hardcoded in GraphQL query) to use as an input.
 * 
 * E.g. 
 * {
 *   page: 1,
 * }
 *
 * @param array<string,mixed>|null $variables
 */
function parseLiteral(
    IntValueNode $value, 
    ?array $variables = null
): int
{
    return parseValue((int) $value->value);
}