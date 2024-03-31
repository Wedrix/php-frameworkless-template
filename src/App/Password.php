<?php

declare(strict_types=1);

namespace App;

final class Password
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
            throw new \InvalidDataException('Invalid Password! The value cannot be empty.');
        }
        
        //TODO: Validate value

        return new self(
            value: $value
        );
    }
}