<?php

declare(strict_types=1);

namespace App\Server\RequestDispatcher;

use App\CipherText;
use Firebase\JWT\JWT;

use function App\AppConfig;
use function App\AuthConfig;

interface Session
{
    public function accessToken(): AccessToken;

    public function contextCookie(): ContextCookie;

    public function refreshToken(): ?RefreshToken;

    public function refresh(): void;
}

function Session(
    AccessToken $accessToken,
    ContextCookie $contextCookie,
    ?RefreshToken $refreshToken
): Session
{
    return new class(
        accessToken: $accessToken,
        contextCookie: $contextCookie,
        refreshToken: $refreshToken
    ) implements Session
    {
        public function __construct(
            private AccessToken $accessToken,
            private ContextCookie $contextCookie,
            private ?RefreshToken $refreshToken
        ){}
    
        public function accessToken(): AccessToken
        {
            return $this->accessToken;
        }
    
        public function contextCookie(): ContextCookie
        {
            return $this->contextCookie;
        }
    
        public function refreshToken(): ?RefreshToken
        {
            return $this->refreshToken;
        }
    
        public function refresh(): void
        {
            $time = \date_create_immutable('now');
    
            $user = UserOfSession(session: $this);

            $request = RequestOfUser($user);
    
            $requestOrigin = requestOrigin($request);
    
            $userContext = \bin2hex(\random_bytes(16));

            if (\is_null($this->refreshToken)) {
                throw new \Exception('The Refresh Token is not set!');
            }
        
            if (RefreshToken::exp($this->refreshToken) <= $time->getTimestamp()) {
                throw new \Exception('The Refresh Token is expired!');
            }
    
            $this->accessToken = AccessToken::{
                JWT::encode(
                    payload: [
                        'iss' => AppConfig()->domain(),
                        'aud' => $requestOrigin,
                        'iat' => $time->getTimestamp(),
                        'exp' => $time->modify('+'. AuthConfig()->accessTokenTTLInMinutes().' minutes')->getTimestamp(),
                        'sub' => (string) $user->id(),
                        'role' => $user->role(),
                        'fingerprint' => \hash_hmac(
                            algo: AuthConfig()->fingerprintHashAlgorithm(),
                            data: $userContext,
                            key: CipherText::decrypt($user->authorizationKey())
                        )
                    ],
                    key: AuthConfig()->signingKey(),
                    alg: AuthConfig()->signingAlgorithm(),
                )
            }();
    
            $this->refreshToken = RefreshToken::{
                JWT::encode(
                    payload: [
                        'iss' => AppConfig()->domain(),
                        'aud' => $requestOrigin,
                        'iat' => $time->getTimestamp(),
                        'exp' => $time->modify('+'.AuthConfig()->refreshTokenTTLInMinutes().' minutes')->getTimestamp(),
                        'sub' => (string) $user->id(),
                        'role' => $user->role(),
                        'fingerprint' => \hash_hmac(
                            algo: AuthConfig()->fingerprintHashAlgorithm(),
                            data: $userContext,
                            key: CipherText::decrypt($user->authorizationKey())
                        )
                    ],
                    key: AuthConfig()->signingKey(),
                    alg: AuthConfig()->signingAlgorithm(),
                )
            }();
    
            $this->contextCookie = ContextCookie::{
                (function() use($userContext): string {
                    $maxAge = AuthConfig()->refreshTokenTTLInMinutes() * 60;
            
                    $cookie = "user_context=$userContext; Max-Age=$maxAge; SameSite=Strict; HttpOnly";
            
                    if (AppConfig()->environment() !== 'development') {
                        $cookie .= '; Secure';
                    }
            
                    return $cookie;
                })()
            }();
        }
    };
}