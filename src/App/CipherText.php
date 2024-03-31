<?php

declare(strict_types=1);

namespace App;

final class CipherText
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
            throw new \InvalidDataException('Invalid CipherText! The value cannot be empty.');
        }

        if (Encrypter()->decrypt($value) === false) {
            throw new \InvalidDataException('Invalid Ciphertext!');
        }

        return new self(
            value: $value
        );
    }

    public static function decrypt(
        self $cipherText
    ): string
    {
        $decryptedValue = Encrypter()->decrypt($cipherText->value);

        if (\is_bool($decryptedValue)) {
            throw new \InvalidDataException("Error decrypting '$cipherText'.");
        }

        return $decryptedValue;
    }
}