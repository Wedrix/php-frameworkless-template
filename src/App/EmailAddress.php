<?php

declare(strict_types=1);

namespace App;

final class EmailAddress
{
    private function __construct(
        private readonly string $value
    )
    {
        if (\filter_var($value, \FILTER_SANITIZE_EMAIL) === false) {
            throw new \Exception('Invalid Email Address value.');
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
}