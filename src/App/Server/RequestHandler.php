<?php

declare(strict_types=1);

namespace App\Server
{
    use App\Id;
    use App\Server\RequestHandler\AccessToken;
    use App\Server\RequestHandler\ContextCookie;
    use App\Server\RequestHandler\SessionOfUser;
    use App\Server\RequestHandler\UserOfRequest;
    use GraphQL\Error\DebugFlag;

    use function App\Config;
    use function App\DoctrineEntityManager;
    use function App\Server\RequestHandler\accessTokenAuthenticatesRequest;
    use function App\Server\RequestHandler\APIRateLimiter;
    use function App\Server\RequestHandler\requestAccessToken;
    use function App\Server\RequestHandler\requestClientIPAddress;
    use function App\Server\RequestHandler\requestOrigin;
    use function App\Server\RequestHandler\requestRefreshToken;
    use function App\Server\RequestHandler\requestUserContext;
    use function App\Server\RequestHandler\Session;
    use function App\Server\RequestHandler\sessionIsNew;
    use function App\Server\RequestHandler\SessionOfUser;
    use function App\Server\RequestHandler\thereIsASessionOfUser;
    use function App\Server\RequestHandler\User;
    use function App\WatchtowerExecutor;

    interface RequestHandler
    {
        public function handle(
            Request $request,
            Response $response
        ): void;
    }
    
    function RequestHandler(): RequestHandler
    {
        static $RequestHandler;

        $RequestHandler ??= new class() implements RequestHandler {
            public function handle(
                Request $request,
                Response $response
            ): void
            {
                // Rate Limit API Access
                try {
                    APIRateLimiter()->checkThatClientIsAllowed(clientIPAddress: requestClientIPAddress(request: $request));
                }
                catch (\ConstraintViolationException) {
                    $response->setStatus(429);

                    return;
                }

                // Attach CORS Headers
                if (!\is_null($origin = requestOrigin($request))) {
                    $allowedOrigins = Config()->accessControlAllowedOrigins();
                    
                    $response->setHeader('Access-Control-Allow-Origin', \in_array($origin, $allowedOrigins) ? $origin : $allowedOrigins[0]);
                    $response->setHeader('Vary', 'Origin');
                    $response->setHeader('Access-Control-Allow-Headers', Config()->accessControlAllowedHeaders());
                    $response->setHeader('Access-Control-Allow-Methods', Config()->accessControlAllowedMethods());
                    $response->setHeader('Access-Control-Expose-Headers', Config()->accessControlExposeHeaders());
        
                    if (Config()->accessControlAllowCredentials()) {
                        $response->setHeader('Access-Control-Allow-Credentials', 'true');
                    }
         
                    $response->setHeader('Content-Type', $request->header('Accept')[0] ?? '*/*');

                    if ($request->method() === 'OPTIONS') {
                        return;
                    }
                }

                // Restore Request Session if Access Token authenticates User
                if (
                    !\is_null($accessToken = requestAccessToken($request)) 
                    && accessTokenAuthenticatesRequest(accessToken: $accessToken, request: $request)
                ) {
                    SessionOfUser::associate(
                        session: Session(
                            accessToken: $accessToken,
                            contextCookie: ContextCookie::{
                                (static function() use($request): string {
                                    $userContext = requestUserContext(request: $request);

                                    $maxAge = Config()->authRefreshTokenTTLInMinutes() * 60;
                            
                                    $cookie = "user_context=$userContext; Max-Age=$maxAge; SameSite=Strict; HttpOnly";
                            
                                    if (Config()->appEnvironment() !== 'development') {
                                        $cookie .= '; Secure';
                                    }
                            
                                    return $cookie;
                                })()
                            }(),
                            refreshToken: requestRefreshToken(request: $request)
                        ),
                        user: $user = User(
                            id: Id::{AccessToken::sub($accessToken)}(),
                            role: AccessToken::role($accessToken)
                        )
                    );
    
                    UserOfRequest::associate(user: $user, request: $request);
                }
                // Else Associate Anonymous User with Request
                else {
                    UserOfRequest::associate(
                        user: $user = User(id: null, role: null),
                        request: $request
                    );
                }
                
                // Handle GraphQL Request
                if ($request->uri()->path() === Config()->appEndpoint()) {
                    $input = (array) $request->parsedBody();

                    if (!isset($input['query']) || empty($input['query'])) {
                        $response->setStatus(400);

                        return;
                    }
    
                    $response->body()
                            ->write(
                                \json_encode(
                                    DoctrineEntityManager()->wrapInTransaction(
                                        static fn() => WatchtowerExecutor()->executeQuery(
                                            source: $input['query'],
                                            rootValue: [],
                                            contextValue: [
                                                'request' => $request,
                                                'response' => $response
                                            ],
                                            variableValues: $input['variables'] ?? null,
                                            operationName: $input['operationName'] ?? null,
                                            validationRules: null
                                        )
                                        ->toArray(
                                            DebugFlag::RETHROW_UNSAFE_EXCEPTIONS
                                        )
                                    )
                                )
                            );
                    
                    $response->setHeader('Content-Type', 'application/json; charset=utf-8');
                }

                // Set session headers
                if (thereIsASessionOfUser(user: $user) && sessionIsNew($session = SessionOfUser(user: $user))) {
                    $response->setHeader('X-Access-Token', (string) $session->accessToken());
                    $response->setHeader('X-Refresh-Token', (string) $session->refreshToken());
                    $response->setHeader('Set-Cookie', (string) $session->contextCookie());
                }

                // Dissociate Request from User
                UserOfRequest::dissociate(user: $user, request: $request);
            }
        };
    
        return $RequestHandler;
    }
}

namespace App\Server\RequestHandler
{
    use App\Hash;
    use App\Id;
    use App\Password;
    use App\Server\Request;

    use function App\Config;
    use function App\Encrypter;

    /**
     * @var \WeakMap<Request,User> $requests_users
     * @var \WeakMap<User,Request> $users_requests
     * @var \WeakMap<Session,User> $sessions_users
     * @var \WeakMap<User,Session> $users_sessions
     */
    global $requests_users, $users_requests, $sessions_users, $users_sessions;
    
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

    function requestIsOfUser(
        Request $request,
        User $user
    ): bool
    {
        global $users_requests;

        return ($users_requests[$user] ?? null) === $request;
    }

    function sessionIsOfUser(
        Session $session,
        User $user
    ): bool
    {
        global $users_sessions;

        return ($users_sessions[$user] ?? null) === $session;
    }

    function userIsOfRequest(
        User $user,
        Request $request
    ): bool
    {
        global $requests_users;

        return ($requests_users[$request] ?? null) === $user;
    }

    function userIsOfSession(
        User $user,
        Session $session
    ): bool
    {
        global $sessions_users;

        return ($sessions_users[$session] ?? null) === $user;
    }

    function userIsAnonymous(
        User $user
    ): bool
    {
        return \is_null($user->role()) || \is_null($user->id());
    }

    function userIsKnown(
        User $user
    ): bool
    {
        if (userIsAnonymous(user: $user)) {
            return false;
        }

        //TODO: Complete this based on the different user roles
        return match($user->role()) {
            default => throw new \Error('Unimplemented functionality!')
        };
    }

    function sessionIsExpired(
        Session $session
    ): bool
    {
		$time = \date_create_immutable('now');
    
        return AccessToken::exp($session->accessToken()) <= $time->getTimestamp();
    }

    function sessionIsNew(
        Session $session
    ): bool
    {
        return AccessToken::iat(accessToken: $session->accessToken()) >= RequestOfUser(user: UserOfSession(session: $session))->time();
    }

    function passwordAuthenticatesUser(
        Password $password,
        User $user
    ): bool
    {
        return Hash::verify((string) $password, AccountOfUser(user: $user)->password());
    }

    function accessTokenAuthenticatesRequest(
        AccessToken $accessToken,
        Request $request
    ): bool
    {
        $time = \date_create_immutable('now');

        $user = User(
            id: Id::{AccessToken::sub($accessToken)}(),
            role: AccessToken::role($accessToken)
        );

        return userIsKnown(user: $user) &&
            (AccessToken::iss($accessToken) === Config()->appDomain()) &&
            (AccessToken::aud($accessToken) === (requestOrigin($request) ?? '')) &&
            (\in_array(AccessToken::aud($accessToken), Config()->accessControlAllowedOrigins())) &&
            (AccessToken::iat($accessToken) <= $time->getTimestamp()) &&
            (AccessToken::exp($accessToken) === $time->setTimestamp(AccessToken::iat($accessToken) + (Config()->authAccessTokenTTLInMinutes() * 60))->getTimestamp()) &&
            (AccessToken::sub($accessToken) === (string) $user->id()) &&
            (AccessToken::role($accessToken) === $user->role()) &&
            (AccessToken::fingerprint($accessToken) === \hash_hmac(
                algo: Config()->authFingerprintHashAlgorithm(),
                data: requestUserContext(request: $request) ?? '',
                key: \is_string($authorizationKey = Encrypter()->decrypt((string) AccountOfUser(user: $user)->authorizationKey())) 
                        ? $authorizationKey 
                        : ''
            ));
    }

    /**
     * Get the client's IP address as determined from the proxy header (X-Forwarded-For or from $request->connection->getRemoteAddress()).
     * @see https://github.com/akrabat/ip-address-middleware/blob/main/src/IpAddress.php
     */
    // TODO: Verify the implementation. It was adapted without much understanding.
    function requestClientIPAddress(
        Request $request
    ): IPAddress
    {
        /**
         * @var bool
         */
        $checkProxyHeaders = Config()->ipAddressParserCheckProxyHeaders();

        /**
         * @var array<string>
         */
        $headersToInspect = Config()->ipAddressParserHeadersToInspect();

        /**
         * @var array<string>
         */
        $trustedProxies = Config()->ipAddressParserTrustedProxies();

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
         * If not empty, then one of these IP addresses must be in $request->connection->getRemoteAddress()
         * in order for the proxy headers to be looked at.
         *
         * @var array<string>
         */
        $trustedProxyIPs = [];

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
                    [$subnet, $bits] = \explode('/', $proxy, 2);
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
                $trustedProxyIPs[] = $proxy;
            }
        }

        $remoteAddress = \extract_ip_address($request->connection->getRemoteAddress());
                        
        if (\is_valid_ip_address($remoteAddress)) {
            $ipAddress = $remoteAddress;
        }

        if ($checkProxyHeaders) {
            $proceedToCheckProxyHeaders = false;

            // Exact Match
            if (!empty($trustedProxyIPs) && \in_array($ipAddress, $trustedProxyIPs)) {
                $proceedToCheckProxyHeaders = true;
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
                        $proceedToCheckProxyHeaders = true;
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
                            $proceedToCheckProxyHeaders = true;
                            break;
                        }
                    }
                }
            }

            if (empty($trustedProxyIPs) && empty($trustedWildcards) && empty($trustedCidrs)) {
                $proceedToCheckProxyHeaders = true;
            }
            
            if ($proceedToCheckProxyHeaders) {
                foreach ($headersToInspect as $header) {
                    if ($request->hasHeader($header)) {
                        $ip = (static function() use($request, $header): string {
                            $items = \explode(',', $request->headerLine($header));
                            $headerValue = \trim(\reset($items));
                    
                            if (\ucfirst($header) === 'Forwarded') {
                                foreach (\explode(';', $headerValue) as $headerPart) {
                                    if (\strtolower(\substr($headerPart, 0, 4)) === 'for=') {
                                        $for = \explode(']', $headerPart);
                                        $headerValue = \trim(\substr(\reset($for), 4), " \t\n\r\0\x0B" . "\"[]");
                                        break;
                                    }
                                }
                            }
                    
                            return \extract_ip_address($headerValue);
                        })();
                        
                        if (\is_valid_ip_address($ip)) {
                            $ipAddress = $ip;
                            break;
                        }
                    }
                }
            }
        }

        return IPAddress::{$ipAddress}();
    }

    function requestOrigin(
        Request $request
    ): ?string
    {
        return $request->header('Origin')[0] ?? null;
    }

    function requestUserContext(
        Request $request
    ): ?string
    {
        return $request->cookieParams()['user_context'] ?? null;
    }

    function requestAccessToken(
        Request $request
    ): ?AccessToken
    {
        $requestAuthorizationHeader = requestAuthorizationHeader($request);

        return (empty($requestAuthorizationHeader) || !\str_starts_with($requestAuthorizationHeader,'Bearer'))
                ? null
                : AccessToken::{
                    \explode('Bearer ', $requestAuthorizationHeader)[1]
                }();
    }

    function requestRefreshToken(
        Request $request
    ): ?RefreshToken
    {
        $requestReauthorizationHeader = requestReauthorizationHeader($request);

        return (empty($requestReauthorizationHeader) || !\str_starts_with($requestReauthorizationHeader,'Bearer'))
                ? null
                : RefreshToken::{
                    \explode('Bearer ', $requestReauthorizationHeader)[1]
                }();
    }

    function requestAuthorizationHeader(
        Request $request
    ): ?string
    {
        return $request->header('Authorization')[0] ?? null;
    }

    function requestReauthorizationHeader(
        Request $request
    ): ?string
    {
        return $request->header('Reauthorization')[0] ?? null;
    }
}