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

final class AccessToken
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

    public static function validate(
        self $accessToken,
        string $requestOrigin,
        string $userContext,
        Id $userId,
        string $userRole,
        CipherText $userAuthorizationKey
    ): bool
    {
        $time = \date_create_immutable('now');

        return ($accessToken->iss === AppConfig()->domain()) &&
            ($accessToken->aud === $requestOrigin) &&
            (\in_array($accessToken->aud, AccessControlConfig()->allowedOrigins())) &&
            ((int) $accessToken->iat <= $time->getTimestamp()) &&
            ((int) $accessToken->exp === $time->setTimestamp((int) $accessToken->iat + (AuthConfig()->accessTokenTTLInMinutes() * 60))->getTimestamp()) &&
            ($accessToken->sub === (string) $userId) &&
            ($accessToken->role === $userRole) &&
            ($accessToken->fingerprint === \hash_hmac(
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
                    'exp' => $time->modify('+'. AuthConfig()->accessTokenTTLInMinutes().' minutes')->getTimestamp(),
                    'sub' => (string) $userId,
                    'role' => (string) $userRole,
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
        self $accessToken
    ): bool
    {
        return empty($accessToken->value);
    }
}