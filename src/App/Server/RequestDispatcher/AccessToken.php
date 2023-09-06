<?php

declare(strict_types=1);

namespace App\Server\RequestDispatcher;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

use function App\AuthConfig;

final class AccessToken
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
            throw new \Exception('Invalid AccessToken! The value cannot be empty.');
        }

        $payload = (array) JWT::decode(
            $value, 
            new Key(
                AuthConfig()->signingKey(), 
                AuthConfig()->signingAlgorithm()
            )
        );

        $iss = $payload['iss'] ?? throw new \Exception('Invalid AccessToken!');
        $aud = $payload['aud'] ?? throw new \Exception('Invalid AccessToken!');
        $iat = $payload['iat'] ?? throw new \Exception('Invalid AccessToken!');
        $exp = $payload['exp'] ?? throw new \Exception('Invalid AccessToken!');
        $sub = $payload['sub'] ?? throw new \Exception('Invalid AccessToken!');
        $role = $payload['role'] ?? throw new \Exception('Invalid AccessToken!');
        $fingerprint = $payload['fingerprint'] ?? throw new \Exception('Invalid AccessToken!');

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
        self $accessToken
    ): string
    {
        return $accessToken->iss;
    }

    public static function aud(
        self $accessToken
    ): string
    {
        return $accessToken->aud;
    }

    public static function iat(
        self $accessToken
    ): int
    {
        return $accessToken->iat;
    }

    public static function exp(
        self $accessToken
    ): int
    {
        return $accessToken->exp;
    }

    public static function sub(
        self $accessToken
    ): string
    {
        return $accessToken->sub;
    }

    public static function role(
        self $accessToken
    ): string
    {
        return $accessToken->role;
    }

    public static function fingerprint(
        self $accessToken
    ): string
    {
        return $accessToken->fingerprint;
    }
}