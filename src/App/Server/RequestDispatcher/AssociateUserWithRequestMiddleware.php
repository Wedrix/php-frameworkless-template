<?php

declare(strict_types=1);

namespace App\Server\RequestDispatcher;

use Comet\Request;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

use function App\Server\RequestDispatcher\UserWithIdAndRole;
use function App\Server\RequestDispatcher\accessTokenAuthenticatesRequest;

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
                throw new \Exception("Invalid request. Must be an instance of \\Comet\\Request");
            }

            $accessToken = AccessToken::{\explode("Bearer ", $request->getHeader('Authorization')[0] ?? '')[1] ?? ''}();
            $firebaseAccessToken = FirebaseAccessToken::{\explode("Bearer ", $request->getHeader('FirebaseAuthorization')[0] ?? '')[1] ?? ''}();
            $refreshToken = RefreshToken::{\explode("Bearer ", $request->getHeader('Reauthorization')[0] ?? '')[1] ?? ''}();
            $requestOrigin = requestOrigin($request);
            $userContext = requestUserContext($request);

            if (
                !AccessToken::empty($accessToken) && !empty($userContext) && !empty($requestOrigin)
            ) {
                if (!accessTokenAuthenticatesRequest($accessToken, $request)) {
                    throw new \Exception('The request could not be authenticated!');
                }

                if (!FirebaseAccessToken::empty($firebaseAccessToken) && !firebaseAccessTokenAuthenticatesRequest($firebaseAccessToken, $request)) {
                    throw new \Exception('The request could not be authenticated!');
                }

                if (!RefreshToken::empty($refreshToken) && !refreshTokenAuthenticatesRequest($refreshToken, $request)) {
                    throw new \Exception('The request could not be authenticated!');
                }
                
                $user = UserWithIdAndRole(
                    id: Id::{AccessToken::sub($accessToken)}(),
                    role: AccessToken::role($accessToken)
                );

                UserOfRequest::associate($user, $request);
            
                SessionOfUser::associate(
                    session: Session(
                        accessToken: $accessToken,
                        firebaseAccessToken: $firebaseAccessToken,
                        refreshToken: $refreshToken,
                        contextCookie: ContextCookie::create($userContext)
                    ),
                    user: $user
                );
            }
    
            return $handler->handle($request);
        }
    };

    return $middleware;
}