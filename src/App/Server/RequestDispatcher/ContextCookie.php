<?php

declare(strict_types=1);

namespace App\Server\RequestDispatcher;

final class ContextCookie
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
            throw new \Exception('Invalid ContextCookie! The value cannot be empty.');
        }

        //TODO: Validate Value

        return new self(
            value: $value
        );
    }
}