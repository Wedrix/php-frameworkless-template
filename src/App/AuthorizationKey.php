<?php

declare(strict_types=1);

namespace App;

final class AuthorizationKey
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
}