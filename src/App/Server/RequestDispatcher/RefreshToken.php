<?php

declare(strict_types=1);

namespace App\Server\RequestDispatcher;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

use function App\AuthConfig;

final class RefreshToken
{
    private function __construct(
        private readonly string $iss,
        private readonly string $aud,
        private readonly int $iat,
        private readonly int $exp,
        private readonly string $sub,
        private readonly string $role,
        private readonly string $fingerprint
    ){}

    public function __toString(): string
    {
        return JWT::encode(
            payload: [
                'iss' => $this->iss,
                'aud' => $this->aud,
                'iat' => $this->iat,
                'exp' => $this->exp,
                'sub' => $this->sub,
                'role' => $this->role,
                'fingerprint' => $this->fingerprint
            ],
            key: AuthConfig()->signingKey(),
            alg: AuthConfig()->signingAlgorithm(),
        );
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
            throw new \Exception('Invalid RefreshToken! The value cannot be empty.');
        }

        $payload = (array) JWT::decode(
            $value,
            new Key(
                AuthConfig()->signingKey(),
                AuthConfig()->signingAlgorithm()
            )
        );

        $iss = $payload['iss'] ?? throw new \Exception('Invalid RefreshToken!');
        $aud = $payload['aud'] ?? throw new \Exception('Invalid RefreshToken!');
        $iat = $payload['iat'] ?? throw new \Exception('Invalid RefreshToken!');
        $exp = $payload['exp'] ?? throw new \Exception('Invalid RefreshToken!');
        $sub = $payload['sub'] ?? throw new \Exception('Invalid RefreshToken!');
        $role = $payload['role'] ?? throw new \Exception('Invalid RefreshToken!');
        $fingerprint = $payload['fingerprint'] ?? throw new \Exception('Invalid RefreshToken!');

        return new self(
            iss: $iss,
            aud: $aud,
            iat: $iat,
            exp: $exp,
            sub: $sub,
            role: $role,
            fingerprint: $fingerprint
        );
    }

    public static function iss(
        self $refreshToken
    ): string
    {
        return $refreshToken->iss;
    }

    public static function aud(
        self $refreshToken
    ): string
    {
        return $refreshToken->aud;
    }

    public static function iat(
        self $refreshToken
    ): int
    {
        return $refreshToken->iat;
    }

    public static function exp(
        self $refreshToken
    ): int
    {
        return $refreshToken->exp;
    }

    public static function sub(
        self $refreshToken
    ): string
    {
        return $refreshToken->sub;
    }

    public static function role(
        self $refreshToken
    ): string
    {
        return $refreshToken->role;
    }

    public static function fingerprint(
        self $refreshToken
    ): string
    {
        return $refreshToken->fingerprint;
    }
}