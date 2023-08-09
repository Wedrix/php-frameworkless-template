<?php

declare(strict_types=1);

namespace App;

final class Id
{
    private function __construct(
        private readonly string $value
    )
    {
        if (!\uuid_is_valid($value)) {
            throw new \Exception("Invalid Id value.");
        }
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

    public static function equalTo(
        self $idA,
        self $idB
    ): bool
    {
        return $idA->value === $idB->value;
    }
}