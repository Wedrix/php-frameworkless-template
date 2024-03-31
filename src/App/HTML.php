<?php

declare(strict_types=1);

namespace App;

final class HTML
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
            throw new \InvalidDataException('Invalid HTML! The value cannot be empty.');
        }

        if ((new \DOMDocument())->loadHTML($value) === false) {
            throw new \InvalidDataException('Invalid HTML!');
        }
        
        return new self(
            value: $value
        );
    }
}