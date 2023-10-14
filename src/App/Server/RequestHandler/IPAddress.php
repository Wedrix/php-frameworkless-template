<?php

declare(strict_types=1);

namespace App\Server\RequestHandler;

final class IPAddress
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
            throw new \Exception('Invalid IPAddress! The value cannot be empty.');
        }
        
        if (\filter_var($value, \FILTER_VALIDATE_IP, \FILTER_FLAG_IPV4 | \FILTER_FLAG_IPV6) === false) {
            throw new \Exception('Invalid IP Address.');
        }

        return new self(
            value: $value
        );
    }
}