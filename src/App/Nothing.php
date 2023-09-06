<?php

declare(strict_types=1);

namespace App;

final class Nothing
{
    private function __construct(
        private readonly string $value
    )
    {
        if (!empty($value)) {
            throw new \Exception('Inavlid EmptyText!');
        }
    }

    public function __toString(): string
    {
        return (string) $this->value;
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