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
    use function App\DataStore;
    use function App\Server\RequestHandler\accessTokenAuthenticatesRequest;
    use function App\Server\RequestHandler\ipAddressAPIRateLimitExceeded;
    use function App\Server\RequestHandler\refreshTokenAuthenticatesRequest;
    use function App\Server\RequestHandler\requestAccessToken;
    use function App\Server\RequestHandler\requestIPAddress;
    use function App\Server\RequestHandler\requestOrigin;
    use function App\Server\RequestHandler\requestRefreshToken;
    use function App\Server\RequestHandler\requestUserContext;
    use function App\Server\RequestHandler\Session;
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
                // Set Request IPAddress
                $request->setAttribute(
                    attribute: Config()->ipAddressParserAttributeName(),
                    value: requestIPAddress(
                        request: $request,
                        checkProxyHeaders: Config()->ipAddressParserCheckProxyHeaders(),
                        headersToInspect: Config()->ipAddressParserHeadersToInspect(),
                        trustedProxies: Config()->ipAddressParserTrustedProxies()
                    ) ?? throw new \Exception('Error resolving the IP Address for this request.')
                );

                // Check Rate Limiting
                if (ipAddressAPIRateLimitExceeded(ipAddress: $request->attribute(Config()->ipAddressParserAttributeName()))) {
                    throw new \Exception('Rate limit exceeded.');
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

                // Restore Request Session If User Logged In
                if (
                    !\is_null($accessToken = requestAccessToken($request)) && !\is_null($userContext = requestUserContext($request))
                ) {
                    if (!accessTokenAuthenticatesRequest(accessToken: $accessToken, request: $request)) {
                        throw new \Exception('The request could not be authenticated!');
                    }
    
                    if (!\is_null($refreshToken = requestRefreshToken($request)) && !refreshTokenAuthenticatesRequest(refreshToken: $refreshToken, request: $request)) {
                        throw new \Exception('The request could not be authenticated!');
                    }
                
                    SessionOfUser::associate(
                        session: Session(
                            accessToken: $accessToken,
                            contextCookie: ContextCookie::{
                                (static function() use($userContext): string {
                                    $maxAge = Config()->authRefreshTokenTTLInMinutes() * 60;
                            
                                    $cookie = "user_context=$userContext; Max-Age=$maxAge; SameSite=Strict; HttpOnly";
                            
                                    if (Config()->appEnvironment() !== 'development') {
                                        $cookie .= '; Secure';
                                    }
                            
                                    return $cookie;
                                })()
                            }(),
                            refreshToken: $refreshToken
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
    
                    $response->body()
                            ->write(
                                \is_string(
                                    $graphQLResult = \json_encode(
                                        WatchtowerExecutor()->executeQuery(
                                            source: $input['query'] ?? throw new \Exception('Empty query.'),
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
                                            debug: (Config()->appEnvironment() === 'development')
                                                ? DebugFlag::INCLUDE_DEBUG_MESSAGE | DebugFlag::INCLUDE_TRACE
                                                : DebugFlag::NONE
                                        )
                                    )
                                ) 
                                ? $graphQLResult
                                : throw new \Exception('Error evaluating GraphQL result.')
                            );
                    
                    $response->setHeader('Content-Type', 'application/json; charset=utf-8');

                    // Clear Data Store
                    DataStore()->clear();
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

    use function App\Cache;
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

    function requestIsNotOfUser(
        Request $request,
        User $user
    ): bool
    {
        global $users_requests;

        return ($users_requests[$user] ?? null) !== $request;
    }

    function sessionIsOfUser(
        Session $session,
        User $user
    ): bool
    {
        return !sessionIsNotOfUser(session: $session, user: $user);
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

        return
            (AccessToken::iss($accessToken) === Config()->appDomain()) &&
            (AccessToken::aud($accessToken) === (requestOrigin($request) ?? throw new \Exception('The origin is not set for the request.'))) &&
            (\in_array(AccessToken::aud($accessToken), Config()->accessControlAllowedOrigins())) &&
            (AccessToken::iat($accessToken) <= $time->getTimestamp()) &&
            (AccessToken::exp($accessToken) === $time->setTimestamp(AccessToken::iat($accessToken) + (Config()->authAccessTokenTTLInMinutes() * 60))->getTimestamp()) &&
            (AccessToken::sub($accessToken) === (string) $user->id()) &&
            (AccessToken::role($accessToken) === $user->role()) &&
            (AccessToken::fingerprint($accessToken) === \hash_hmac(
                algo: Config()->authFingerprintHashAlgorithm(),
                data: requestUserContext(request: $request) ?? throw new \Exception('The user context is not set for the request.'),
                key: \is_string($authorizationKey = Encrypter()->decrypt((string) AccountOfUser(user: $user)->authorizationKey())) 
                        ? $authorizationKey 
                        : throw new \Exception('Error decrypting the authorization key.')
            ));
    }

    function refreshTokenAuthenticatesRequest(
        RefreshToken $refreshToken,
        Request $request
    ): bool
    {
        $time = \date_create_immutable('now');

        $user = User(
            id: Id::{RefreshToken::sub($refreshToken)}(),
            role: RefreshToken::role($refreshToken)
        );

        return 
            (RefreshToken::iss($refreshToken) === Config()->appDomain()) &&
            (RefreshToken::aud($refreshToken) === (requestOrigin($request) ?? throw new \Exception('The origin is not set for the request.'))) &&
            (\in_array(RefreshToken::aud($refreshToken), Config()->accessControlAllowedOrigins())) &&
            (RefreshToken::iat($refreshToken) <= $time->getTimestamp()) &&
            (RefreshToken::exp($refreshToken) === $time->setTimestamp(RefreshToken::iat($refreshToken) + (Config()->authRefreshTokenTTLInMinutes() * 60))->getTimestamp()) &&
            (RefreshToken::sub($refreshToken) === (string) $user->id()) &&
            (RefreshToken::role($refreshToken) === $user->role()) &&
            (RefreshToken::fingerprint($refreshToken) === \hash_hmac(
                algo: Config()->authFingerprintHashAlgorithm(),
                data: requestUserContext(request: $request) ?? throw new \Exception('The user context is not set for the request.'),
                key: \is_string($authorizationKey = Encrypter()->decrypt((string) AccountOfUser(user: $user)->authorizationKey())) 
                        ? $authorizationKey 
                        : throw new \Exception('Error decrypting the authorization key.')
            ));
    }

    /**
     * Get the client's IP address as determined from the proxy header (X-Forwarded-For or from $request->connection->_remoteAddress
     * @see https://github.com/akrabat/ip-address-middleware/blob/main/src/IpAddress.php
     * 
     * @param Request $request The request instance
     * @param bool $checkProxyHeaders Whether to use proxy headers to determine client IP address
     * @param array<string> $headersToInspect List of proxy headers inspected for the client IP address
     * @param array<string> $trustedProxies List of trusted proxy addresses (accepts wildcards and CIDR notation)
     */
    // TODO: Verify the implementation. It was adapted without much understanding.
    function requestIPAddress(
        Request $request,
        bool $checkProxyHeaders,
        array $headersToInspect,
        array $trustedProxies
    ): ?IPAddress
    {
        if ($checkProxyHeaders && empty($trustedProxies)) {
            throw new \Exception('Use of the forward headers requires an array for trusted proxies.');
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

        /**
         * Connection::getRemoteAddress() returns string
         * @see https://github.com/walkor/workerman/blob/f3856199e0105eb66b35dc4c7d091e2283e4b682/src/Connection/ConnectionInterface.php#L124C16-L124C16 
         */
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

        return empty($ipAddress)
            ? null
            : IPAddress::{$ipAddress}();
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

        \assert(
            \is_null($requestAuthorizationHeader) || !empty($requestAuthorizationHeader), 
            new \Exception('Invalid \'Authorization\' header!')
        );

        return \is_null($requestAuthorizationHeader)
                ? null
                : AccessToken::{
                    \explode('Bearer ', $requestAuthorizationHeader)[1] 
                        ?? throw new \Exception('Invalid \'Authorization\' header!')
                }();
    }

    function requestRefreshToken(
        Request $request
    ): ?RefreshToken
    {
        $requestReauthorizationHeader = requestReauthorizationHeader($request);

        \assert(
            \is_null($requestReauthorizationHeader) || !empty($requestReauthorizationHeader), 
            new \Exception('Invalid \'Reauthorization\' header!')
        );

        return \is_null($requestReauthorizationHeader)
                ? null
                : RefreshToken::{
                    \explode('Bearer ', $requestReauthorizationHeader)[1] 
                        ?? throw new \Exception('Invalid \'Reauthorization\' header!')
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

    /**
     * Uses the Sliding Window algorithm
     */
    function ipAddressAPIRateLimitExceeded(
        IPAddress $ipAddress
    ): bool
    {
        $time = \date_create_immutable('now');

        // Fetch user accesses
        $cacheItem = Cache()->getItem(key: "ip_address_{$ipAddress}_api_access");

        $userAccesses = $cacheItem->isHit() 
                                ? $cacheItem->get() 
                                : [];

        // Add current timestamp as new user access
        $userAccesses[] = WindowAccess(
            timestamp: $time->getTimestamp()
        );

        // Filter user accesses where timestamps < current timestamp - access window size in seconds
        $userAccesses = \array_filter(
            $userAccesses,
            static fn(WindowAccess $access) => $access->timestamp() >= ($time->getTimestamp() - Config()->accessControlApiAccessWindowSizeInSeconds())
        );

        // Save filtered user accesses
        Cache()->save(
            $cacheItem->set($userAccesses)
                    ->expiresAfter(Config()->accessControlApiAccessWindowSizeInSeconds())
        );

        // Count filtered user accesses
        $accessCount = \count($userAccesses);

        // Exceeded if user accesses count > limit
        if ($accessCount > Config()->accessControlApiAccessLimit()) {
            return true;
        }

        return false;
    }
}