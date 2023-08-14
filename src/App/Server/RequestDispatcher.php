<?php

declare(strict_types=1);

namespace App\Server
{
    use Comet\Factory\CometPsr17Factory;
    use Slim\App as RequestDispatcher;
    use Slim\Factory\AppFactory;
    use Slim\Factory\Psr17\Psr17FactoryProvider;
    
    use function App\AppConfig;
    use function App\Server\RequestDispatcher\AddIPAddressToRequestMiddleware;
    use function App\Server\RequestDispatcher\AssociateUserWithRequestMiddleware;
    use function App\Server\RequestDispatcher\CheckIfRequestIsPermittedMiddleware;
    use function App\Server\RequestDispatcher\CleanUpAfterRequestMiddleware;
    use function App\Server\RequestDispatcher\GraphQLController;
    use function App\Server\RequestDispatcher\HandleOptionsRequestAndAddCORSHeadersMIddleware;
    
    function RequestDispatcher(): RequestDispatcher
    {
        static $requestDispatcher;
        
        $requestDispatcher ??= (static function (): RequestDispatcher {
            Psr17FactoryProvider::setFactories([CometPsr17Factory::class]);
            AppFactory::setPsr17FactoryProvider(new Psr17FactoryProvider());
    
            $requestDispatcher = AppFactory::create();
    
            // See https://www.slimframework.com/docs/v4/concepts/middleware.html for the middleware execution order
            $requestDispatcher->add(AssociateUserWithRequestMiddleware());
            $requestDispatcher->add(CheckIfRequestIsPermittedMiddleware());
            $requestDispatcher->add(AddIPAddressToRequestMiddleware());
            $requestDispatcher->add(CleanUpAfterRequestMiddleware());
            $requestDispatcher->add(HandleOptionsRequestAndAddCORSHeadersMIddleware());
    
            $requestDispatcher->post(AppConfig()->endpoint(), GraphQLController());
    
            return $requestDispatcher;
        })();
    
        return $requestDispatcher;
    }
}

namespace App\Server\RequestDispatcher
{
    use App\Hash;
    use App\Password;
    use Comet\Request;

    /**
     * @var \WeakMap<Request,User>
     */
    global $requests_users;
    
    /**
     * @var \WeakMap<User,Request>
     */
    global $users_requests;
    
    /**
     * @var \WeakMap<Session,User>
     */
    global $sessions_users;
    
    /**
     * @var \WeakMap<User,Session>
     */
    global $users_sessions;
    
    $requests_users = new \WeakMap();
    $users_requests = new \WeakMap();
    $sessions_users = new \WeakMap();
    $users_sessions = new \WeakMap();

    function thereIsAUserOfSession(
        Session $session
    ): bool
    {
        global $sessions_users;

        return isset($sessions_users[$session]);
    }

    function thereIsAUserOfRequest(
        Request $request
    ): bool
    {
        global $requests_users;

        return isset($requests_users[$request]);
    }

    function thereIsASessionOfUser(
        User $user
    ): bool
    {
        global $users_sessions;

        return isset($users_sessions[$user]);
    }

    function thereIsARequestOfUser(
        User $user
    ): bool
    {
        global $users_requests;
        
        return isset($users_requests[$user]);
    }

    function thereIsNoUserOfSession(
        Session $session
    ): bool
    {
        return !thereIsAUserOfSession(session: $session);
    }

    function thereIsNoUserOfRequest(
        Request $request
    ): bool
    {
        return !thereIsAUserOfRequest(request: $request);
    }

    function thereIsNoSessionOfUser(
        User $user
    ): bool
    {
        return !thereIsASessionOfUser(user: $user);
    }

    function thereIsNoRequestOfUser(
        User $user
    ): bool
    {
        return !thereIsARequestOfUser(user: $user);
    }

    function requestIsNotOfUser(
        Request $request,
        User $user
    ): bool
    {
        global $users_requests;

        return ($users_requests[$user] ?? null) !== $request;
    }

    function sessionIsNotOfUser(
        Session $session,
        User $user
    ): bool
    {
        global $users_sessions;

        return ($users_sessions[$user] ?? null) !== $session;
    }

    function userIsNotOfRequest(
        User $user,
        Request $request
    ): bool
    {
        global $requests_users;

        return ($requests_users[$request] ?? null) !== $user;
    }

    function userIsNotOfSession(
        User $user,
        Session $session
    ): bool
    {
        global $sessions_users;

        return ($sessions_users[$session] ?? null) !== $user;
    }

    function sessionIsExpired(
        Session $session
    ): bool
    {
		$time = \date_create_immutable('now');
    
        return AccessToken::exp($session->accessToken()) <= $time->getTimestamp();
    }

    /**
     * Get the client's IP address as determined from the proxy header (X-Forwarded-For or from $request->connection->_remoteAddress
     * @see https://github.com/akrabat/ip-address-middleware/blob/main/src/IpAddress.php
     * 
     * @param Request $request The request instance
     * @param string $attributeName Name of attribute added to ServerRequest object
     * @param bool $checkProxyHeaders Whether to use proxy headers to determine client IP address
     * @param array<string> $headersToInspect List of proxy headers inspected for the client IP address
     * @param array<string> $trustedProxies List of trusted proxy addresses (accepts wildcards and CIDR notation)
     */
    function requestIPAddress(
        Request $request,
        string $attributeName,
        bool $checkProxyHeaders,
        array $headersToInspect,
        array $trustedProxies
    ): IPAddress
    {
        if ($checkProxyHeaders && empty($trustedProxies)) {
            throw new \InvalidArgumentException('Use of the forward headers requires an array for trusted proxies.');
        }

        /**
         * List of trusted proxy IP wildcard ranges
         *
         * @var array<array<string>>
         */
        $trustedWildcards = [];

        /**
         * List of trusted proxy IP CIDR ranges
         *
         * @var array<array<int>>
         */
        $trustedCidrs = [];
        
        /**
         * List of trusted proxy IP addresses
         *
         * If not empty, then one of these IP addresses must be in $_SERVER['REMOTE_ADDR']
         * in order for the proxy headers to be looked at.
         *
         * @var array<string>
         */
        $trustedIps = [];

        /**
         * @var string
         */
        $ipAddress = '';

        foreach ($trustedProxies as $proxy) {
            if (\strpos($proxy, '*') !== false) {
                /**
                 * @return array<string>
                 */
                $trustedWildcards[] = (static function() use ($proxy): array {
                    // IPv4 has 4 parts separated by '.'
                    // IPv6 has 8 parts separated by ':'
                    if (\strpos($proxy, '.') > 0) {
                        $delim = '.';
                        $parts = 4;
                    } else {
                        $delim = ':';
                        $parts = 8;
                    }
            
                    return \explode($delim, $proxy, $parts);
                })();
            }
        }

        foreach ($trustedProxies as $proxy) {
            if (\strpos($proxy, '/') > 6) {
                /**
                 * @return array<int>
                 */
                $trustedCidrs[] = (static function() use ($proxy): array {
                    list($subnet, $bits) = \explode('/', $proxy, 2);
                    $subnet = \ip2long($subnet);
                    $mask = -1 << (32 - (int) $bits);
                    $min = $subnet & $mask;
                    $max = $subnet | ~$mask;
            
                    return [$min, $max];
                })();
            }
        }

        foreach ($trustedProxies as $proxy) {
            if (!\in_array($proxy, $trustedWildcards) && !\in_array($proxy, $trustedCidrs)) {
                $trustedIps[] = $proxy;
            }
        }

        if (IPAddress::isValid($remoteAddr = IPAddress::extract($request->connection->getRemoteAddress()))) {
            $ipAddress = $remoteAddr;
        }

        if ($checkProxyHeaders) {
            // Exact Match
            if (\in_array($ipAddress, $trustedIps)) {
                $checkProxyHeaders = true;
            }

            // Wildcard Match
            if (!empty($trustedWildcards)) {
                // IPv4 has 4 parts separated by '.'
                // IPv6 has 8 parts separated by ':'
                if (\strpos($ipAddress, '.') > 0) {
                    $delim = '.';
                    $parts = 4;
                } else {
                    $delim = ':';
                    $parts = 8;
                }

                $ipAddrParts = \explode($delim, $ipAddress, $parts);
                foreach ($trustedWildcards as $proxy) {
                    if (\count($proxy) !== $parts) {
                        continue; // IP version does not match
                    }
                    $match = true;
                    foreach ($proxy as $i => $part) {
                        if ($part !== '*' && $part !== $ipAddrParts[$i]) {
                            $match = false;
                            break; // IP does not match, move to next proxy
                        }
                    }
                    if ($match) {
                        $checkProxyHeaders = true;
                        break;
                    }
                }
            }

            // CIDR Match
            if (!empty($trustedCidrs)) {
                // Only IPv4 is supported for CIDR matching
                $ipAsLong = \ip2long($ipAddress);
                if ($ipAsLong) {
                    foreach ($trustedCidrs as $proxy) {
                        if ($proxy[0] <= $ipAsLong && $ipAsLong <= $proxy[1]) {
                            $checkProxyHeaders = true;
                            break;
                        }
                    }
                }
            }

            if (empty($trustedIps) && empty($trustedWildcards) && empty($trustedCidrs)) {
                $checkProxyHeaders = true;
            }
            
            foreach ($headersToInspect as $header) {
                if ($request->hasHeader($header)) {
                    $ip = (static function () use($request, $header): string {
                        $items = \explode(',', $request->getHeaderLine($header));
                        $headerValue = \trim(\reset($items));
                
                        if (\ucfirst($header) == 'Forwarded') {
                            foreach (\explode(';', $headerValue) as $headerPart) {
                                if (\strtolower(\substr($headerPart, 0, 4)) == 'for=') {
                                    $for = \explode(']', $headerPart);
                                    $headerValue = \trim(\substr(\reset($for), 4), " \t\n\r\0\x0B" . "\"[]");
                                    break;
                                }
                            }
                        }
                
                        return IPAddress::{IPAddress::extract($headerValue)}();
                    })();
                    
                    if (IPAddress::isValid($ip)) {
                        $ipAddress = $ip;
                        break;
                    }
                }
            }
        }

        if ($ipAddress === '') {
            throw new \Exception("Error resolving the $attributeName for this request.");
        }

        return IPAddress::{$ipAddress}();
    }

    function passwordAuthenticatesUser(
        Password $password,
        User $user
    ): bool
    {
        return Hash::verify((string) $password, $user->password());
    }

    function accessTokenAuthenticatesRequest(
        AccessToken $accessToken,
        Request $request
    ): bool
    {
        return AccessToken::validate(
            $accessToken,
            requestOrigin: requestOrigin($request),
            userContext: requestUserContext($request),
            userId: (
                $user = UserWithIdAndRole(
                    id: Id::{AccessToken::sub($accessToken)}(),
                    role: AccessToken::role($accessToken)
                )
            )->id(),
            userRole: $user->role(),
            userAuthorizationKey: $user->authorizationKey()
        );
    }

    function firebaseAccessTokenAuthenticatesRequest(
        FirebaseAccessToken $firebaseAccessToken,
        Request $request
    ): bool
    {
        return FirebaseAccessToken::validate(
            $firebaseAccessToken,
            userId: (
                $user = UserWithIdAndRole(
                    id: Id::{FirebaseAccessToken::sub($firebaseAccessToken)}(),
                    role: FirebaseAccessToken::claims($firebaseAccessToken)['role'] ?? ''
                )
            )->id(),
            userRole: $user->role(),
        );
    }

    function refreshTokenAuthenticatesRequest(
        RefreshToken $refreshToken,
        Request $request
    ): bool
    {
        return RefreshToken::validate(
            $refreshToken,
            requestOrigin: requestOrigin($request),
            userContext: requestUserContext($request),
            userId: (
                $user = UserWithIdAndRole(
                    id: Id::{RefreshToken::sub($refreshToken)}(),
                    role: RefreshToken::role($refreshToken)
                )
            )->id(),
            userRole: $user->role(),
            userAuthorizationKey: $user->authorizationKey()
        );
    }

    function requestUserContext(
        Request $request
    ): string
    {
        return $request->getCookieParams()['user_context'] ?? '';
    }

    function requestOrigin(
        Request $request
    ): string
    {
        return $request->getHeader('Origin')[0] ?? '';
    }
}