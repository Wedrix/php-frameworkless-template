<?php

declare(strict_types=1);

namespace App\Server\RequestDispatcher;

use App\CipherText;
use App\Id;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

use function App\AccessControlConfig;
use function App\AppConfig;
use function App\AuthConfig;

final class RefreshToken
{
    private readonly string $iss;

    private readonly string $aud;

    private readonly int $iat;

    private readonly int $exp;

    private readonly string $sub;

    private readonly string $role;

    private readonly string $fingerprint;

    private function __construct(
        private readonly string $value
    )
    {
        $payload = !empty($value) 
            ? (array) JWT::decode(
                $value, 
                new Key(
                    AuthConfig()->signingKey(), 
                    AuthConfig()->signingAlgorithm()
                )
            )
            : [];

        $this->iss = $payload['iss'] ?? '';

        $this->aud = $payload['aud'] ?? '';

        $this->iat = $payload['iat'] ?? 0;

        $this->exp = $payload['exp'] ?? 0;

        $this->sub = $payload['sub'] ?? '';

        $this->role = $payload['role'] ?? '';

        $this->fingerprint = $payload['fingerprint'] ?? '';
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

    public static function validate(
        self $refreshToken,
        string $requestOrigin,
        string $userContext,
        Id $userId,
        string $userRole,
        CipherText $userAuthorizationKey
    ): bool
    {
        $time = \date_create_immutable('now');

        return ($refreshToken->iss === AppConfig()->domain()) &&
            ($refreshToken->aud === $requestOrigin) &&
            (in_array($refreshToken->aud, AccessControlConfig()->allowedOrigins())) &&
            ((int) $refreshToken->iat <= $time->getTimestamp()) &&
            ((int) $refreshToken->exp === \date_create_immutable('now')->setTimestamp((int) $refreshToken->iat + (AuthConfig()->refreshTokenTTLInMinutes() * 60))->getTimestamp()) &&
            ($refreshToken->sub === (string) $userId) &&
            ($refreshToken->role === $userRole) &&
            ($refreshToken->fingerprint === \hash_hmac(
                algo: 'sha256',
                data: $userContext,
                key: (string) $userAuthorizationKey
            ));
    }

    public static function create(
        string $requestOrigin,
        string $userContext,
        Id $userId,
        string $userRole,
        CipherText $userAuthorizationKey
    ): self
    {
        $time = \date_create_immutable('now');

        return self::{
            JWT::encode(
                payload: [
                    'iss' => AppConfig()->domain(),
                    'aud' => $requestOrigin,
                    'iat' => $time->getTimestamp(),
                    'exp' => $time->modify('+'.AuthConfig()->refreshTokenTTLInMinutes().' minutes')->getTimestamp(),
                    'sub' => $userId,
                    'role' => $userRole,
                    'fingerprint' => \hash_hmac(
                        algo: 'sha256',
                        data: $userContext,
                        key: CipherText::decrypt($userAuthorizationKey)
                    )
                ],
                key: AuthConfig()->signingKey(),
                alg: 'HS256',
            )
        }();
    }

    public static function empty(
        self $refreshToken
    ): bool
    {
        return empty($refreshToken->value);
    }
}