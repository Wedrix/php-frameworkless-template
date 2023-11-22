<?php

declare(strict_types=1);

namespace App\Server\RequestHandler;

use App\CipherText;
use Firebase\JWT\JWT;

use function App\Config;

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

            if (\is_null($this->refreshToken)) {
                throw new \Exception('The Refresh Token is not set!');
            }
        
            if (RefreshToken::exp($this->refreshToken) <= $time->getTimestamp()) {
                throw new \Exception('The Refresh Token is expired!');
            }
    
            $this->accessToken = AccessToken::{
                JWT::encode(
                    payload: [
                        'iss' => Config()->appDomain(),
                        'aud' => $requestOrigin = requestOrigin($request) ?? throw new \Exception('The origin is not set for the request.'),
                        'iat' => $time->getTimestamp(),
                        'exp' => $time->modify('+'. Config()->authAccessTokenTTLInMinutes().' minutes')->getTimestamp(),
                        'sub' => (string) $user->id(),
                        'role' => $user->role(),
                        'fingerprint' => \hash_hmac(
                            algo: Config()->authFingerprintHashAlgorithm(),
                            data: $userContext = \bin2hex(\random_bytes(Config()->accessControlUserContextKeyLength())),
                            key: CipherText::decrypt($authorizationKey = AccountOfUser(user: $user)->authorizationKey())
                        )
                    ],
                    key: Config()->authSigningKey(),
                    alg: Config()->authSigningAlgorithm(),
                )
            }();
    
            $this->refreshToken = RefreshToken::{
                JWT::encode(
                    payload: [
                        'iss' => Config()->appDomain(),
                        'aud' => $requestOrigin,
                        'iat' => $time->getTimestamp(),
                        'exp' => $time->modify('+'.Config()->authRefreshTokenTTLInMinutes().' minutes')->getTimestamp(),
                        'sub' => (string) $user->id(),
                        'role' => $user->role(),
                        'fingerprint' => \hash_hmac(
                            algo: Config()->authFingerprintHashAlgorithm(),
                            data: $userContext,
                            key: CipherText::decrypt($authorizationKey)
                        )
                    ],
                    key: Config()->authSigningKey(),
                    alg: Config()->authSigningAlgorithm(),
                )
            }();
    
            $this->contextCookie = ContextCookie::{
                (static function() use($userContext): string {
                    $maxAge = Config()->authRefreshTokenTTLInMinutes() * 60;
            
                    $cookie = "user_context=$userContext; Max-Age=$maxAge; SameSite=Strict; HttpOnly";
            
                    if (Config()->appEnvironment() !== 'development') {
                        $cookie .= '; Secure';
                    }
            
                    return $cookie;
                })()
            }();
        }
    };
}