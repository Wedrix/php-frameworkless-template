<?php

declare(strict_types=1);

namespace App\Server\RequestDispatcher;

use App\Id;
use Comet\Request;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

use function App\AppConfig;
use function App\AuthConfig;

function AssociateUserWithRequestMiddleware(): Middleware
{
    static $middleware;
    
    $middleware ??= new class() implements Middleware {
        public function __invoke(
            ServerRequestInterface $request, 
            RequestHandlerInterface $handler
        ): ResponseInterface
        {
            if (!$request instanceof Request) {
                throw new \Exception('Invalid request. Must be an instance of \\Comet\\Request.');
            }

            $accessToken = requestAccessToken($request);
            $refreshToken = requestRefreshToken($request);
            $userContext = requestUserContext($request);

            if (
                !\is_null($accessToken) && !\is_null($userContext)
            ) {
                if (!accessTokenAuthenticatesRequest(accessToken: $accessToken, request: $request)) {
                    throw new \Exception('The request could not be authenticated!');
                }

                if (!\is_null($refreshToken) && !refreshTokenAuthenticatesRequest(refreshToken: $refreshToken, request: $request)) {
                    throw new \Exception('The request could not be authenticated!');
                }
                
                $user = UserWithIdAndRole(
                    id: Id::{AccessToken::sub($accessToken)}(),
                    role: AccessToken::role($accessToken)
                );

                $session = Session(
                    accessToken: $accessToken,
                    contextCookie: ContextCookie::{
                        (static function() use($userContext): string {
                            $maxAge = AuthConfig()->refreshTokenTTLInMinutes() * 60;
                    
                            $cookie = "user_context=$userContext; Max-Age=$maxAge; SameSite=Strict; HttpOnly";
                    
                            if (AppConfig()->environment() !== 'development') {
                                $cookie .= '; Secure';
                            }
                    
                            return $cookie;
                        })()
                    }(),
                    refreshToken: $refreshToken
                );
            
                SessionOfUser::associate(
                    session: $session,
                    user: $user
                );

                UserOfRequest::associate($user, $request);
            }
    
            return $handler->handle($request);
        }
    };

    return $middleware;
}