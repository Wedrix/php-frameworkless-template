<?php

declare(strict_types=1);

namespace App\Server\RequestDispatcher;

final class IPAddress
{
    private function __construct(
        private readonly string $value
    )
    {
        if (\filter_var($value, \FILTER_VALIDATE_IP, \FILTER_FLAG_IPV4 | \FILTER_FLAG_IPV6) === false) {
            throw new \Exception('Invalid IP Address.');
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

    public static function isValid(
        string $value
    ): bool
    {
        return \filter_var($value, \FILTER_VALIDATE_IP, \FILTER_FLAG_IPV4 | \FILTER_FLAG_IPV6) !== false;
    }

    /**
     * Remove port from IPV4 address if it exists
     *
     * Note: leaves IPV6 addresses alone
     */
    public static function extract(
        string $value
    ): string
    {
        $parts = \explode(':', $value);
        
        if (\count($parts) == 2) {
            if (\filter_var($parts[0], \FILTER_VALIDATE_IP, \FILTER_FLAG_IPV4) !== false) {
                return $parts[0];
            }
        }

        return $value;
    }
}