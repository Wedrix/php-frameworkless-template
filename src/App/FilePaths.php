<?php

declare(strict_types=1);

namespace App;

final class FilePaths
{
    /**
     * @param array<int,FilePath> $elements
     */
    private function __construct(
        private readonly array $elements
    ){}

    public function __toString(): string
    {
        return \implode(',',$this->elements);
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
            throw new \InvalidDataException('Invalid FilePaths! The value cannot be empty.');
        }

        $filePathsParts = \explode(',',$value);

        $elements = \array_map(
            static fn(string $filePathPart) => FilePath::{$filePathPart}(),
            $filePathsParts
        );

        return new self(
            elements: $elements
        );
    }
}