<?php

declare(strict_types=1);

namespace App;

final class Text
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
            throw new \Exception('Invalid Text! The value cannot be empty.');
        }

        if ($value !== \strip_tags_with_content($value)) {
            throw new \Exception('Invalid Text! The value cannot contain HTML tags.');
        }

        return new self(
            value: $value
        );
    }

    public static function same(
        self $textA,
        self $textB
    ): bool
    {
        return $textA->value === $textB->value;
    }

    public static function different(
        self $textA,
        self $textB
    ): bool
    {
        return $textA->value !== $textB->value;
    }
}