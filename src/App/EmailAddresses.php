<?php

declare(strict_types=1);

namespace App;

final class EmailAddresses
{
    /**
     * @readonly
     * @param array<int,EmailAddress> $elements
     * 
     * Cannot directly use readonly keyword due a caveat of laravel/serializable-closures.
     * @see https://github.com/laravel/serializable-closure#caveats
     */
    private function __construct(
        private array $elements
    ){}

    public function __toString(): string
    {
        return \implode(',',$this->elements);
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
            throw new \InvalidDataException('Invalid EmailAddresses! The value cannot be empty.');
        }

        $emailAddressesParts = \explode(',',$value);

        $elements = \array_map(
            static fn(string $emailAddressPart) => EmailAddress::{$emailAddressPart}(),
            $emailAddressesParts
        );

        return new self(
            elements: $elements
        );
    }
}