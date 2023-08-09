<?php

declare(strict_types=1);

namespace App\Server\RequestDispatcher;

use function App\AppConfig;
use function App\AuthConfig;

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
        return new self(
            value: $name
        );
    }

    public static function create(
        string $userContext
    ): self
    {
        $maxAge = AuthConfig()->refreshTokenTTLInMinutes() * 60;

        $cookie = "user_context=$userContext; Max-Age=$maxAge; SameSite=Strict; HttpOnly";

        if (AppConfig()->environment() !== 'development') {
            $cookie .= '; Secure';
        }

        return self::{$cookie}();
    }
}