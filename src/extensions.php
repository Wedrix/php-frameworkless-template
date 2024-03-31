<?php

declare(strict_types=1);



// String //

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
            return \preg_replace('@<(?!(?:'. \implode('|', $tags) .')\b)(\w+)\b.*?>.*?</\1>@si', '', $text);
        }
        else {
            return \preg_replace('@<('. \implode('|', $tags) .')\b.*?>.*?</\1>@si', '', $text);
        }
    }
    else if($invert === false) {
      return \preg_replace('@<(\w+)\b.*?>.*?</\1>@si', '', $text);
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



// Array //

/**
 * @param array<int|string,mixed> $needles
 * @param array<int|string,mixed> $haystack
 */
function any_in_array(
    array $needles, 
    array $haystack
): bool
{
    foreach ($needles as $needle) {
        if (\in_array($needle, $haystack)) {
            return true;
        }
    }

    return false;
}

/**
 * @param array<int|string,mixed> $needles
 * @param array<int|string,mixed> $haystack
 */
function all_in_array(
    array $needles, 
    array $haystack
): bool
{
    foreach ($needles as $needle) {
        if (!\in_array($needle, $haystack)) {
            return false;
        }
    }

    return true;
}



// File //

/**
 * @see https://www.php.net/manual/en/function.file-put-contents.php#123657
 * 
 * @param resource $context
 */
function file_force_put_contents(
    string $filename,
    mixed $data,
    int $flags = 0,
    $context = null
): int|false
{
    $parts = \explode('/', $filename);
    \array_pop($parts);
    $dir = \implode('/', $parts);

    if (!\is_dir($dir)) {
        \mkdir($dir, 0777, true);
    }

    return \file_put_contents($filename, $data, $flags);
}