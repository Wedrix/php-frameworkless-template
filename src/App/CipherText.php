<?php

declare(strict_types=1);

namespace App;

final class CipherText
{
    private function __construct(
        private readonly string $value
    )
    {
        if (!\is_string(Encrypter()->decrypt($value))) {
            throw new \Exception('Invalid Ciphertext.');
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

    public static function decrypt(
        self $cipherText
    ): string
    {
        $decryptedValue = Encrypter()->decrypt($cipherText->value);

        if (\is_bool($decryptedValue)) {
            throw new \Exception("Error decrypting '$cipherText'.");
        }

        return $decryptedValue;
    }
}