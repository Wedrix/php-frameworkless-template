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
    string $tags = '', 
    bool $invert = false
): string
{
    \preg_match_all('/<(.+?)[\s]*\/?[\s]*>/si', \trim($tags), $tags);
  
    $tags = \array_unique($tags[1]);
  
    if (\is_array($tags) && (\count($tags) > 0)) {
        if ($invert == false) {
            return \preg_replace('@<(?!(?:'. \implode('|', $tags) .')\b)(\w+)\b.*?>.*?</\1>@si', '', $text) 
                ?? throw new \Exception("Error parsing '$text'.");
        }
        else {
            return \preg_replace('@<('. \implode('|', $tags) .')\b.*?>.*?</\1>@si', '', $text) 
                ?? throw new \Exception("Error parsing '$text'.");
        }
    }
    else if($invert == false) {
      return \preg_replace('@<(\w+)\b.*?>.*?</\1>@si', '', $text) 
        ?? throw new \Exception("Error parsing '$text'.");
    }
    
    return $text;
}