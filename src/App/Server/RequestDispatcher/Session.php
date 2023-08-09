<?php

declare(strict_types=1);

namespace App\Server\RequestDispatcher;

interface Session
{
    public function accessToken(): AccessToken;

    public function firebaseAccessToken(): FirebaseAccessToken;

    public function refreshToken(): RefreshToken;

    public function contextCookie(): ContextCookie;

    public function refresh(): void;
}

function Session(
    AccessToken $accessToken,
    FirebaseAccessToken $firebaseAccessToken,
    RefreshToken $refreshToken,
    ContextCookie $contextCookie
): Session
{
    return new class(
        accessToken: $accessToken,
        firebaseAccessToken: $firebaseAccessToken,
        refreshToken: $refreshToken,
        contextCookie: $contextCookie
    ) implements Session
    {
        public function __construct(
            private AccessToken $accessToken,
            private FirebaseAccessToken $firebaseAccessToken,
            private RefreshToken $refreshToken,
            private ContextCookie $contextCookie
        ){}
    
        public function accessToken(): AccessToken
        {
            return $this->accessToken;
        }
    
        public function firebaseAccessToken(): FirebaseAccessToken
        {
            return $this->firebaseAccessToken;
        }
    
        public function refreshToken(): RefreshToken
        {
            return $this->refreshToken;
        }
    
        public function contextCookie(): ContextCookie
        {
            return $this->contextCookie;
        }
    
        public function refresh(): void
        {
            $time = \date_create_immutable('now');
    
            $user = UserOfSession(session: $this);
    
            $request = RequestOfUser(user: $user);
    
            $requestOrigin = $request->getHeader('Origin')[0] ?? '';
    
            if (RefreshToken::empty($this->refreshToken)) {
                throw new \Exception('Refresh Token not set!');
            }
        
            if (RefreshToken::exp($this->refreshToken) <= $time->getTimestamp()) {
                throw new \Exception('Refresh Token expired!');
            }
    
            $userContext = \bin2hex(\random_bytes(16));
    
            $this->accessToken = AccessToken::create(
                requestOrigin: $requestOrigin,
                userContext: $userContext,
                userId: $user->id(),
                userRole: $user->role(),
                userAuthorizationKey: $user->authorizationKey()
            );
    
            $this->firebaseAccessToken = FirebaseAccessToken::create(
                userId: $user->id(),
                userRole: $user->role(),
            );
    
            $this->refreshToken = RefreshToken::create(
                requestOrigin: $requestOrigin,
                userContext: $userContext,
                userId: $user->id(),
                userRole: $user->role(),
                userAuthorizationKey: $user->authorizationKey()
            );
    
            $this->contextCookie = ContextCookie::create($userContext);
        }
    };
}