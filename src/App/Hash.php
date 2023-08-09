<?php

declare(strict_types=1);

namespace App;

final class Hash
{
    private function __construct(
        private readonly string $value
    )
    {
        //TODO: Validate value
    }

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
        return new self(
            value: $name
        );
    }

    public static function verify(
        string $value,
        self $hash
    ): bool
    {
        return Hasher()->verify($value, $hash->value);
    }
}