<?php

declare(strict_types=1);

namespace App\Server\RequestDispatcher;

use App\CipherText;
use App\Hash;
use App\Id;
use Comet\Request;
use Firebase\JWT\JWT;

use function App\AccessControlConfig;
use function App\AppConfig;
use function App\AuthConfig;

interface User
{
    public function id(): Id;

    public function role(): string;

    public function authorizationKey(): CipherText;

    public function password(): Hash;

    public function startSession(
        Request $request
    ): void;

    public function refreshSession(
        Session $session
    ): void;
}

function User(
    Id $id,
    string $role,
    CipherText $authorizationKey,
    Hash $password
): User
{
    return new class(
        id: $id,
        role: $role,
        authorizationKey: $authorizationKey,
        password: $password
    ) implements User
    {
        public function __construct(
            private readonly Id $id,
            private readonly string $role,
            private readonly CipherText $authorizationKey,
            private readonly Hash $password
        ){}
        
        public function id(): Id
        {
            return $this->id;
        }

        public function role(): string
        {
            return $this->role;
        }

        public function authorizationKey(): CipherText
        {
            return $this->authorizationKey;
        }

        public function password(): Hash
        {
            return $this->password;
        }

        public function startSession(
            Request $request
        ): void
        {
            UserOfRequest::associate(
                user: $this,
                request: $request
            );
        
            SessionOfUser::associate(
                session: Session(
                    accessToken: AccessToken::{
                        JWT::encode(
                            payload: [
                                'iss' => AppConfig()->domain(),
                                'aud' => $requestOrigin = requestOrigin($request) ?? throw new \Exception('The origin is not set for the request.'),
                                'iat' => ($time = \date_create_immutable('now'))->getTimestamp(),
                                'exp' => $time->modify('+'. AuthConfig()->accessTokenTTLInMinutes().' minutes')->getTimestamp(),
                                'sub' => (string) $this->id,
                                'role' => $this->role,
                                'fingerprint' => \hash_hmac(
                                    algo: AuthConfig()->fingerprintHashAlgorithm(),
                                    data: $userContext = \bin2hex(\random_bytes(AccessControlConfig()->userContextKeyLength())),
                                    key: CipherText::decrypt($this->authorizationKey)
                                )
                            ],
                            key: AuthConfig()->signingKey(),
                            alg: AuthConfig()->signingAlgorithm(),
                        )
                    }(),
                    refreshToken: RefreshToken::{
                        JWT::encode(
                            payload: [
                                'iss' => AppConfig()->domain(),
                                'aud' => $requestOrigin,
                                'iat' => $time->getTimestamp(),
                                'exp' => $time->modify('+'.AuthConfig()->refreshTokenTTLInMinutes().' minutes')->getTimestamp(),
                                'sub' => (string) $this->id,
                                'role' => $this->role,
                                'fingerprint' => \hash_hmac(
                                    algo: AuthConfig()->fingerprintHashAlgorithm(),
                                    data: $userContext,
                                    key: CipherText::decrypt($this->authorizationKey)
                                )
                            ],
                            key: AuthConfig()->signingKey(),
                            alg: AuthConfig()->signingAlgorithm(),
                        )
                    }(),
                    contextCookie: ContextCookie::{
                        (static function() use($userContext): string {
                            $maxAge = AuthConfig()->refreshTokenTTLInMinutes() * 60;
                    
                            $cookie = "user_context=$userContext; Max-Age=$maxAge; SameSite=Strict; HttpOnly";
                    
                            if (AppConfig()->environment() !== 'development') {
                                $cookie .= '; Secure';
                            }
                    
                            return $cookie;
                        })()
                    }()
                ),
                user: $this
            );
        }

        public function refreshSession(
            Session $session
        ): void
        {
            assert(sessionIsOfUser(session: $session, user: $this), new \Exception('The session is not of the user.'));

            $session->refresh();
        }
    };
}