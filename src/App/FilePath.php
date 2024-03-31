<?php

declare(strict_types=1);

namespace App;

/**
 * @see http://regexr.com/7sr3l
 */
final class FilePath
{
    private function __construct(
        private readonly string $value
    ){}

    public function __toString(): string
    {
        return $this->value;
    }

    /**
     * @param array<string> $arguments
     */
    public static function __callStatic(
        string $name, 
        array $arguments
    ): self
    {
        $value = \trim($name);

        if (empty($value)) {
            throw new \InvalidDataException('Invalid FilePath! The value cannot be empty.');
        }

        // Note: We need to escape '\' twice '\\\' in PHP for some reason
        if (!\preg_match('/^((\w+:((\\\|\/)[\w\s-]+)+(.\w+)?)|((\/[\w\s-]+)+(.\w+)?))$/', $value)) {
            throw new \InvalidDataException('Invalid FilePath!');
        }

        return new self(
            value: $value
        );
    }
}