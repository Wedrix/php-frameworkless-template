<?php

declare(strict_types=1);

namespace App;

/**
 * @see http://regexr.com/7ng41
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
            throw new \Exception('Invalid DirectoryPath! The value cannot be empty.');
        }

        if (!\preg_match('/(((\w+:(\\|\/))|(\w+:((\\|\/)\w+)+))|(\\|\/)|(((\\|\/)\w+)*))/', $value)) {
            throw new \Exception('Invalid DirectoryPath!');
        }

        return new self(
            value: $value
        );
    }
}