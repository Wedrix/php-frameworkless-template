<?php

declare(strict_types=1);

namespace App\Server;

use GuzzleHttp\Psr7\Utils;
use Psr\Http\Message\StreamInterface;
use Workerman\Protocols\Http\Request as WorkermanRequest;

/**
 * @property \Workerman\Connection\TcpConnection $connection
 */
#[\AllowDynamicProperties]
final class Request
{
    /** @var array<string,string[]> Map of all registered headers, as original name => array of values */
    private array $headers;

    /** @var array<string,string> Map of lowercase header name => original name at registration */
    private array $headerNames;

    private string $protocolVersion;

    private StreamInterface $stream;

    private string $method;

    private URI $uri;

    /** @var array<string,mixed> */
    private array $attributes;

    /** @var array<string,mixed> */
    private array $cookieParams;

    /** @var null|array<string,mixed>|object */
    private null|array|object $parsedBody;

    /** @var array<string,mixed> */
    private array $queryParams;

    /** @var array<string,mixed> */
    private array $uploadedFiles;

    private ?string $requestTarget;

    private int $time;

    public function __construct(
        string $httpBuffer
    ) 
    {
        $request = new WorkermanRequest($httpBuffer);
        $headers = $request->header();
        $body = $request->rawBody();
        $files = $request->file();
        $queryParams = $request->get();
        $cookieParams = $request->cookie();
        $uri = $request->uri();
        $method = $request->method();

        $this->protocolVersion = '1.1';
        $this->uri = new URI($uri);
        $this->stream = Utils::streamFor($body);
        $this->method = \strtoupper($method);
        $this->uploadedFiles = $files;
        $this->queryParams = $queryParams;
        $this->cookieParams = $cookieParams;
        $this->requestTarget = null;
        $this->time = \date_create_immutable('now')->getTimestamp();

        $this->setHeaders($headers);

        $this->setParsedBody($headers,$body);

        if (!isset($this->headerNames['host'])) {
            $this->updateHostFromUri();
        }
    }

    public function protocolVersion(): string
    {
        return $this->protocolVersion;
    }

    /**
     * @return array<string,string[]> 
     */
    public function headers(): array
    {
        return $this->headers;
    }

    public function hasHeader(
        string $header
    ): bool
    {
        return isset($this->headerNames[\strtolower($header)]);
    }

    /**
     * @return string[]
     */
    public function header(
        string $header
    ): array
    {
        $header = \strtolower($header);

        if (!isset($this->headerNames[$header])) {
            return [];
        }

        $header = $this->headerNames[$header];

        return $this->headers[$header];
    }

    public function headerLine(
        string $header
    ): string
    {
        return \implode(', ', $this->header($header));
    }

    /**
     * 
     * @param string|string[] $value
     */
    public function addHeader(
        string $header, 
        string|array $value
    ): void
    {
        $normalized = \strtolower($header);

        if (isset($this->headerNames[$normalized])) {
            $header = $this->headerNames[$normalized];

            $this->headers[$header] = \array_merge($this->headers[$header], \is_string($value) ? [$value] : $value);
        } 
        else {
            $this->headerNames[$normalized] = $header;

            $this->headers[$header] = \is_string($value) ? [$value] : $value;
        }
    }

    public function removeHeader(
        string $header
    ): void
    {
        $normalized = \strtolower($header);

        if (!isset($this->headerNames[$normalized])) {
            return;
        }

        $header = $this->headerNames[$normalized];

        unset($this->headers[$header], $this->headerNames[$normalized]);
    }

    public function body(): StreamInterface
    {
        return $this->stream;
    }

    /**
     * @return array<string,mixed>
     */
    public function uploadedFiles(): array
    {
        return $this->uploadedFiles;
    }

    /**
     * @return array<string,mixed>
     */
    public function cookieParams(): array
    {
        return $this->cookieParams;
    }

    /**
     * @return array<string,mixed>
     */
    public function queryParams(): array
    {
        return $this->queryParams;
    }

    /**
     * @return null|array<string,mixed>|object
     */
    public function parsedBody(): null|array|object
    {
        return $this->parsedBody;
    }

    /**
     * @return array<string,mixed>
     */
    public function attributes(): array
    {
        return $this->attributes;
    }

    public function attribute(
        string $attribute, 
        mixed $default = null
    ): mixed
    {
        if (false === \array_key_exists($attribute, $this->attributes)) {
            return $default;
        }

        return $this->attributes[$attribute];
    }

    public function setAttribute(
        string $attribute, 
        mixed $value
    ): void
    {
        $this->attributes[$attribute] = $value;
    }

    public function removeAttribute(
        string $attribute
    ): void
    {
        if (false === \array_key_exists($attribute, $this->attributes)) {
            return;
        }

        unset($this->attributes[$attribute]);
    }

    public function method(): string
    {
        return $this->method;
    }

    public function uri(): URI
    {
        return $this->uri;
    }

    public function requestTarget(): string
    {
        if ($this->requestTarget !== null) {
            return $this->requestTarget;
        }

        $target = $this->uri->path();
        if ($target === '') {
            $target = '/';
        }
        if ($this->uri->query() != '') {
            $target .= '?' . $this->uri->query();
        }

        return $target;
    }

    public function time(): int
    {
        return $this->time;
    }

    /**
     * @param array<string,string|string[]> $headers
     */
    private function setHeaders(
        array $headers
    ): void
    {
        $this->headers = [];
        $this->headerNames = [];

        foreach ($headers as $header => $value) {
            $value = \is_array($value) ? \array_values($value) : \array_values([$value]);

            $normalized = \strtolower($header);

            if (isset($this->headerNames[$normalized])) {
                $header = $this->headerNames[$normalized];

                $this->headers[$header] = \array_merge($this->headers[$header], $value);
            } else {
                $this->headerNames[$normalized] = $header;

                $this->headers[$header] = $value;
            }
        }
    }

    /**
     * @param array<string,string|string[]> $headers
     */
    private function setParsedBody(
       array $headers,
       string $body
    ): void
    {
        $this->parsedBody = null;
        
        // --- Parse POST forms and JSON bodies
        if (\array_key_exists('content-type', $headers)) {
            $contentTypeHeaders = \is_string($contentTypeHeaders = $headers['content-type']) ? [$contentTypeHeaders] : $contentTypeHeaders;

            foreach ($contentTypeHeaders as $_ => $contentTypeHeader) {
                if (\strstr($contentTypeHeader, 'application/json')) {
                    $this->parsedBody = \json_decode($body, true);
                } else if (\strstr($contentTypeHeader, 'application/x-www-form-urlencoded')) {
                    \parse_str($body, $this->parsedBody);
                }
            }
        }
    }

    private function updateHostFromUri(): void
    {
        $host = $this->uri->host();

        if ($host === '') {
            return;
        }

        if (($port = $this->uri->port()) !== null) {
            $host .= ':' . $port;
        }

        if (isset($this->headerNames['host'])) {
            $header = $this->headerNames['host'];
        } else {
            $header = 'Host';
            $this->headerNames['host'] = 'Host';
        }
        // Ensure Host is the first header.
        // See: http://tools.ietf.org/html/rfc7230#section-5.4
        $this->headers = [$header => [$host]] + $this->headers;
    }
}
