<?php

declare(strict_types=1);

namespace App\Server\RequestDispatcher;

use App\Id;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

use function App\AuthConfig;
use function App\FirebaseConfig;

final class FirebaseAccessToken
{
    private readonly string $iss;

    private readonly string $aud;

    private readonly int $iat;

    private readonly int $exp;

    private readonly string $sub;

    private readonly string $uid;

    /**
     * @var array<string,mixed>
     */
    private readonly array $claims;

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

        $this->uid = $payload['uid'] ?? '';

        $this->claims = $payload['claims'] ?? [];
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
        self $firebaseAccessToken
    ): string
    {
        return $firebaseAccessToken->iss;
    }

    public static function aud(
        self $firebaseAccessToken
    ): string
    {
        return $firebaseAccessToken->aud;
    }

    public static function iat(
        self $firebaseAccessToken
    ): int
    {
        return $firebaseAccessToken->iat;
    }

    public static function exp(
        self $firebaseAccessToken
    ): int
    {
        return $firebaseAccessToken->exp;
    }

    public static function sub(
        self $firebaseAccessToken
    ): string
    {
        return $firebaseAccessToken->sub;
    }

    public static function uid(
        self $firebaseAccessToken
    ): string
    {
        return $firebaseAccessToken->uid;
    }

    /**
     * @return array<string,mixed>
     */
    public static function claims(
        self $firebaseAccessToken
    ): array
    {
        return $firebaseAccessToken->claims;
    }

    public static function validate(
        self $firebaseAccessToken,
        Id $userId,
        string $userRole
    ): bool
    {
        $time = \date_create_immutable('now');

        return ($firebaseAccessToken->iss === FirebaseConfig()->serviceAccountEmail()) &&
            ($firebaseAccessToken->sub === FirebaseConfig()->serviceAccountEmail()) &&
            ($firebaseAccessToken->aud === 'https://identitytoolkit.googleapis.com/google.identity.identitytoolkit.v1.IdentityToolkit') &&
            ((int) $firebaseAccessToken->iat <= $time->getTimestamp()) &&
            ((int) $firebaseAccessToken->exp === \date_create_immutable('now')->setTimestamp((int) $firebaseAccessToken->iat + (AuthConfig()->accessTokenTTLInMinutes() * 60))->getTimestamp()) &&
            ($firebaseAccessToken->uid === (string) $userId) &&
            (($firebaseAccessToken->claims['role'] ?? '') === $userRole);
    }

    public static function create(
        Id $userId,
        string $userRole
    ): self
    {
        $time = \date_create_immutable('now');

        return self::{
            JWT::encode(
                payload: [
                    'iss' => FirebaseConfig()->serviceAccountEmail(),
                    'sub' => FirebaseConfig()->serviceAccountEmail(),
                    'aud' => 'https://identitytoolkit.googleapis.com/google.identity.identitytoolkit.v1.IdentityToolkit',
                    'iat' => $time->getTimestamp(),
                    'exp' => $time->modify('+'.AuthConfig()->accessTokenTTLInMinutes().' minutes')->getTimestamp(),  // Maximum expiration time is one hour
                    'uid' => $userId,
                    'claims' => [
                      'role' => $userRole
                    ]
                ],
                key: FirebaseConfig()->signingKey(),
                alg: 'RS256',
            )
        }();
    }

    public static function empty(
        self $firebaseAccessToken
    ): bool
    {
        return empty($firebaseAccessToken->value);
    }
}