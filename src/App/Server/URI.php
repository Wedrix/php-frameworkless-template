<?php

declare(strict_types=1);

namespace App\Server;

final class URI
{
    /** @var array<int,string> Standard ports and supported schemes */
    private const SCHEMES = [80 => 'http', 443 => 'https'];

    private string $scheme;

    private string $userInfo;

    private string $host;

    private ?int $port;

    private string $path;

    private string $query;

    private string $fragment;

    private ?string $cache;

    /**
     * @param string $uri
     */
    public function __construct(
        string $uri = ''
    )
    {
        if ($uri === '') {
            return;
        }

        if (($uri = \parse_url($uri)) === false) {
            throw new \Exception('The source URI string appears to be malformed.');
        }

        $this->scheme = isset($uri['scheme']) ? $this->normalizeScheme($uri['scheme']) : '';
        $this->userInfo = isset($uri['user']) ? $this->normalizeUserInfo($uri['user'], $uri['pass'] ?? null) : '';
        $this->host = isset($uri['host']) ? $this->normalizeHost($uri['host']) : '';
        $this->port = isset($uri['port']) ? $this->normalizePort($uri['port']) : null;
        $this->path = isset($uri['path']) ? $this->normalizePath($uri['path']) : '';
        $this->query = isset($uri['query']) ? $this->normalizeQuery($uri['query']) : '';
        $this->fragment = isset($uri['fragment']) ? $this->normalizeFragment($uri['fragment']) : '';
        $this->cache = null;
    }

    public function __clone()
    {
        $this->cache = null;
    }

    public function __toString(): string
    {
        if (\is_string($this->cache)) {
            return $this->cache;
        }

        $this->cache = '';

        if ($this->scheme !== '') {
            $this->cache .= $this->scheme . ':';
        }

        if (($authority = $this->authority()) !== '') {
            $this->cache .= '//' . $authority;
        }

        if ($this->path !== '') {
            if ($authority === '') {
                // If the path is starting with more than one "/" and no authority is present,
                // the starting slashes MUST be reduced to one.
                $this->cache .= $this->path[0] === '/' ? '/' . \ltrim($this->path, '/') : $this->path;
            } else {
                // If the path is rootless and an authority is present, the path MUST be prefixed by "/".
                $this->cache .= $this->path[0] === '/' ? $this->path : '/' . $this->path;
            }
        }

        if ($this->query !== '') {
            $this->cache .= '?' . $this->query;
        }

        if ($this->fragment !== '') {
            $this->cache .= '#' . $this->fragment;
        }

        return $this->cache;
    }

    public function scheme(): string
    {
        return $this->scheme;
    }

    public function authority(): string
    {
        if (($authority = $this->host) === '') {
            return '';
        }

        if ($this->userInfo !== '') {
            $authority = $this->userInfo . '@' . $authority;
        }

        if ($this->isNotStandardPort()) {
            $authority .= ':' . $this->port;
        }

        return $authority;
    }

    public function userInfo(): string
    {
        return $this->userInfo;
    }

    public function host(): string
    {
        return $this->host;
    }

    public function port(): ?int
    {
        return $this->isNotStandardPort() ? $this->port : null;
    }

    public function path(): string
    {
        if ($this->path === '' || $this->path === '/') {
            return $this->path;
        }

        if ($this->path[0] !== '/') {
            // If the path is rootless and an authority is present, the path MUST be prefixed by "/".
            return $this->host === '' ? $this->path : '/' . $this->path;
        }

        return '/' . \ltrim($this->path, '/');
    }

    public function query(): string
    {
        return $this->query;
    }

    public function fragment(): string
    {
        return $this->fragment;
    }

    public function setScheme(
        string $scheme
    ): void
    {
        $scheme = $this->normalizeScheme($scheme);

        if ($scheme === $this->scheme) {
            return;
        }

        $this->scheme = $scheme;
    }

    public function setUserInfo(
        string $user, 
        ?string $password = null
    ): void
    {
        $userInfo = $this->normalizeUserInfo($user, $password);

        if ($userInfo === $this->userInfo) {
            return;
        }

        $this->userInfo = $userInfo;
    }

    public function setHost(
        string $host
    ): void
    {
        $host = $this->normalizeHost($host);

        if ($host === $this->host) {
            return;
        }

        $this->host = $host;
    }

    public function setPort(
        ?int $port
    ): void
    {
        $port = $this->normalizePort($port);

        if ($port === $this->port) {
            return;
        }

        $this->port = $port;
    }

    public function setPath(
        string $path
    ): void
    {
        $path = $this->normalizePath($path);

        if ($path === $this->path) {
            return;
        }

        $this->path = $path;
    }

    public function setQuery(
        string $query
    ): void
    {
        $query = $this->normalizeQuery($query);

        if ($query === $this->query) {
            return;
        }

        $this->query = $query;
    }

    public function setFragment(
        string $fragment
    ): void
    {
        $fragment = $this->normalizeFragment($fragment);

        if ($fragment === $this->fragment) {
            return;
        }

        $this->fragment = $fragment;
    }

    private function normalizeScheme(
        string $scheme
    ): string
    {
        if (!$scheme = \preg_replace('#:(//)?$#', '', \strtolower($scheme))) {
            return '';
        }

        if (!\in_array($scheme, static::SCHEMES, true)) {
            throw new \InvalidArgumentException(\sprintf(
                'Unsupported scheme "%s". It must be an empty string or any of "%s".',
                $scheme,
                \implode('", "', static::SCHEMES)
            ));
        }

        return $scheme;
    }

    private function normalizeUserInfo(
        string $user, 
        ?string $pass = null
    ): string
    {
        if ($user === '') {
            return '';
        }

        $pattern = '/(?:[^%a-zA-Z0-9_\-\.~\pL!\$&\'\(\)\*\+,;=]+|%(?![A-Fa-f0-9]{2}))/u';
        $userInfo = $this->encode($user, $pattern);

        if ($pass !== null) {
            $userInfo .= ':' . $this->encode($pass, $pattern);
        }

        return $userInfo;
    }

    private function normalizeHost(
        string $host
    ): string
    {
        return \strtr($host, 'ABCDEFGHIJKLMNOPQRSTUVWXYZ', 'abcdefghijklmnopqrstuvwxyz');
    }

    private function normalizePort(
        int|string|null $port
    ): ?int
    {
        if ($port === null) {
            return null;
        }

        if (\is_string($port) && !\is_numeric($port)) {
            throw new \Exception("Invalid port '$port' specified. It must be an integer, an integer string, or null.");
        }

        $port = (int) $port;

        if ($port < 1 || $port > 65535) {
            throw new \Exception("Invalid port '$port' specified. It must be a valid TCP/UDP port in range 1..65535.");
        }

        return $port;
    }

    private function normalizePath(
        string $path
    ): string
    {
        if ($path === '' || $path === '/') {
            return $path;
        }

        return $this->encode($path, '/(?:[^a-zA-Z0-9_\-\.~!\$&\'\(\)\*\+,;=%:@\/]++|%(?![A-Fa-f0-9]{2}))/');
    }

    private function normalizeQuery(
        string $query
    ): string
    {
        if ($query === '' || $query === '?') {
            return '';
        }

        if ($query[0] === '?') {
            $query = ltrim($query, '?');
        }

        return $this->encode($query, '/(?:[^a-zA-Z0-9_\-\.~!\$&\'\(\)\*\+,;=%:@\/\?]++|%(?![A-Fa-f0-9]{2}))/');
    }

    private function normalizeFragment(
        string $fragment
    ): string
    {
        if ($fragment === '' || $fragment === '#') {
            return '';
        }

        if ($fragment[0] === '#') {
            $fragment = ltrim($fragment, '#');
        }

        return $this->encode($fragment, '/(?:[^a-zA-Z0-9_\-\.~!\$&\'\(\)\*\+,;=%:@\/\?]++|%(?![A-Fa-f0-9]{2}))/');
    }

    private function encode(
        string $string, 
        string $pattern
    ): string
    {
        return (string) \preg_replace_callback(
            $pattern,
            static fn (array $matches) => \rawurlencode($matches[0]),
            $string,
        );
    }

    private function isNotStandardPort(): bool
    {
        if ($this->port === null) {
            return false;
        }

        return (!isset(static::SCHEMES[$this->port]) || $this->scheme !== static::SCHEMES[$this->port]);
    }
}