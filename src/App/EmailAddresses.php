<?php

declare(strict_types=1);

namespace App;

final class EmailAddresses
{
    private function __construct(
        /**
         * @readonly
         * @var array<int,EmailAddress>
         * 
         * Cannot use readonly keyword due a caveat of laravel/serializable-closures.
         * @see https://github.com/laravel/serializable-closure#caveats
         */
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
        $emailAddressStrings = \explode(',',$name);

        $emailAddresses = \array_map(
            static fn(string $emailAddressString): EmailAddress => EmailAddress::{$emailAddressString}(),
            $emailAddressStrings
        );

        return new self(
            elements: $emailAddresses
        );
    }
}