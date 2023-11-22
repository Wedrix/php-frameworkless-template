<?php

declare(strict_types=1);

function pluralize(
    string $word
): string
{
    return Inflector()->pluralize($word);
}

function singularize(
    string $word
): string
{
    return Inflector()->singularize($word);
}

function classify(
    string $word
): string
{
    return Inflector()->classify($word);
}

function capitalize(
    string $word
): string
{
    return Inflector()->capitalize($word);
}

/**
 * @see https://www.php.net/manual/en/function.strip-tags.php#86964
 */
function strip_tags_with_content(
    string $text, 
    string $allowed_tags = '', 
    bool $invert = false
): string
{
    \preg_match_all('/<(.+?)[\s]*\/?[\s]*>/si', \trim($allowed_tags), $tags);
  
    $tags = \array_unique($tags[1]);
  
    if (\count($tags) > 0) {
        if ($invert === false) {
            return \preg_replace('@<(?!(?:'. \implode('|', $tags) .')\b)(\w+)\b.*?>.*?</\1>@si', '', $text) 
                ?? throw new \Exception("Error parsing '$text'.");
        }
        else {
            return \preg_replace('@<('. \implode('|', $tags) .')\b.*?>.*?</\1>@si', '', $text) 
                ?? throw new \Exception("Error parsing '$text'.");
        }
    }
    else if($invert === false) {
      return \preg_replace('@<(\w+)\b.*?>.*?</\1>@si', '', $text) 
        ?? throw new \Exception("Error parsing '$text'.");
    }
    
    return $text;
}

function is_valid_ip_address(
    string $string
): bool
{
    return \filter_var($string, \FILTER_VALIDATE_IP, \FILTER_FLAG_IPV4 | \FILTER_FLAG_IPV6) !== false;
}

/**
 * Remove port from IPV4 address if it exists
 *
 * Note: leaves IPV6 addresses alone
 */
function extract_ip_address(
    string $string
): string
{
    $parts = \explode(':', $string);
    
    if (\count($parts) === 2) {
        if (is_valid_ip_address($parts[0])) {
            return $parts[0];
        }
    }

    return $string;
}

/**
 * Determines is a url string, directory string, or file path string is absolute.
 * @see https://github.com/symfony/symfony/blob/6.3/src/Symfony/Component/Filesystem/Path.php#L364
 */
function is_absolute_path(
    string $path
): bool
{
    if ('' === $path) {
        return false;
    }

    // Strip scheme
    if (false !== $schemeSeparatorPosition = strpos($path, '://')) {
        $path = substr($path, $schemeSeparatorPosition + 3);
    }

    $firstCharacter = $path[0];

    // UNIX root "/" or "\" (Windows style)
    if ('/' === $firstCharacter || '\\' === $firstCharacter) {
        return true;
    }

    // Windows root
    if (\strlen($path) > 1 && ctype_alpha($firstCharacter) && ':' === $path[1]) {
        // Special case: "C:"
        if (2 === \strlen($path)) {
            return true;
        }

        // Normal case: "C:/ or "C:\"
        if ('/' === $path[2] || '\\' === $path[2]) {
            return true;
        }
    }

    return false;
}