<?php

declare(strict_types=1);

namespace App;

final class Text
{
    private readonly string $value;

    private function __construct(
        string $value
    )
    {
        $this->value = \strip_tags_with_content($value);
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
        self $textA,
        self $textB
    ): bool
    {
        return $textA->value === $textB->value;
    }
}