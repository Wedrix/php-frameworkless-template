<?php

declare(strict_types=1);

namespace App\Server\RequestHandler;

use App\CipherText;
use Firebase\JWT\JWT;

use function App\Config;
use function App\Encrypter;

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
                throw new \ConstraintViolationException('The refresh token is unset!');
            }
    
            if (
                !(
                    (RefreshToken::iss($this->refreshToken) === Config()->appDomain()) &&
                    (RefreshToken::aud($this->refreshToken) === (requestOrigin($request) ?? '')) &&
                    (\in_array(RefreshToken::aud($this->refreshToken), Config()->accessControlAllowedOrigins())) &&
                    (RefreshToken::iat($this->refreshToken) <= $time->getTimestamp()) &&
                    (RefreshToken::exp($this->refreshToken) === $time->setTimestamp(RefreshToken::iat($this->refreshToken) + (Config()->authRefreshTokenTTLInMinutes() * 60))->getTimestamp()) &&
                    (RefreshToken::sub($this->refreshToken) === (string) $user->id()) &&
                    (RefreshToken::role($this->refreshToken) === $user->role()) &&
                    (RefreshToken::fingerprint($this->refreshToken) === \hash_hmac(
                        algo: Config()->authFingerprintHashAlgorithm(),
                        data: requestUserContext(request: $request) ?? '',
                        key: \is_string($authorizationKey = Encrypter()->decrypt((string) AccountOfUser(user: $user)->authorizationKey())) 
                                ? $authorizationKey 
                                : ''
                    ))
                )
            ) {
                throw new \ConstraintViolationException('The refresh token is invalid!');
            }
        
            if (RefreshToken::exp($this->refreshToken) <= $time->getTimestamp()) {
                throw new \ConstraintViolationException('The refresh token has expired!');
            }
    
            $this->accessToken = AccessToken::{
                JWT::encode(
                    payload: [
                        'iss' => Config()->appDomain(),
                        'aud' => $requestOrigin = requestOrigin($request) ?? '',
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
                        'exp' => $time->modify('+'. Config()->authRefreshTokenTTLInMinutes().' minutes')->getTimestamp(),
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