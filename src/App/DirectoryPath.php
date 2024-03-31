<?php

declare(strict_types=1);

namespace App;

/**
 * @see http://regexr.com/7sr2t
 */
final class DirectoryPath
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
            throw new \InvalidDataException('Invalid DirectoryPath! The value cannot be empty.');
        }

        // Note: We need to escape '\' twice '\\\' in PHP for some reason
        if (!\preg_match('/^((\w+:(\\\|\/))|(\w+:((\\\|\/)[\w\s-]+)+)|(\/)|((\/[\w\s-]+)+))$/', $value)) {
            throw new \InvalidDataException('Invalid DirectoryPath!');
        }

        return new self(
            value: $value
        );
    }
}