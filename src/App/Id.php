<?php

declare(strict_types=1);

namespace App;

final class Id
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
            throw new \InvalidDataException('Invalid Id! The value cannot be empty.');
        }

        if (!\uuid_is_valid($value)) {
            throw new \InvalidDataException("Invalid Id value.");
        }

        return new self(
            value: $value
        );
    }

    public static function same(
        self $idA,
        self $idB
    ): bool
    {
        return $idA->value === $idB->value;
    }

    public static function different(
        self $idA,
        self $idB
    ): bool
    {
        return $idA->value !== $idB->value;
    }
}