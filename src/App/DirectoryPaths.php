<?php

declare(strict_types=1);

namespace App;

final class DirectoryPaths
{
    /**
     * @param array<int,DirectoryPath> $elements
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
            throw new \InvalidDataException('Invalid DirectoryPaths! The value cannot be empty.');
        }

        $directoryPathsParts = \explode(',',$value);

        $elements = \array_map(
            static fn(string $directoryPathPart) => DirectoryPath::{$directoryPathPart}(),
            $directoryPathsParts
        );

        return new self(
            elements: $elements
        );
    }
}