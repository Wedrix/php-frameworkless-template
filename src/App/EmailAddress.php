<?php

declare(strict_types=1);

namespace App;

final class EmailAddress
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
            throw new \Exception('Invalid EmailAddress! The value cannot be empty.');
        }

        if (\filter_var($value, \FILTER_VALIDATE_EMAIL) === false) {
            throw new \Exception('Invalid EmailAddress!');
        }

        return new self(
            value: $value
        );
    }
}