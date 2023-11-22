<?php

declare(strict_types=1);

namespace App\Server;

use GuzzleHttp\Psr7\Utils;
use Psr\Http\Message\StreamInterface;

use function App\Config;

final class Response
{
    /** @var array<string,string[]> Map of all registered headers, as original name => array of values */
    private array $headers;

    /** @var array<string,string> Map of lowercase header name => original name at registration */
    private array $headerNames;

    private string $protocolVersion;

    private StreamInterface $stream;

    private int $statusCode;

    private string $reasonPhrase;

    /**
     * @param int                                  $statusCode  Status code
     * @param string|null                          $reasonPhrase  Reason phrase
     * @param array<string,string>                 $headers Response headers
     * @param string|null|resource|StreamInterface $body    Response body
     */
    public function __construct(
        int $statusCode = 200,
        string|null $reasonPhrase = null,
        array $headers = [],
        $body = null
    ) 
    {
        $this->protocolVersion = '1.1';

        $this->statusCode = $statusCode;

        $this->stream = Utils::streamFor($body);

        $this->reasonPhrase = (static function() use($statusCode,$reasonPhrase): string {
            if (\is_null($reasonPhrase) && isset(Config()->appHttpPhrases()[$statusCode])) {
                return Config()->appHttpPhrases()[$statusCode];
            }
            return (string) $reasonPhrase;
        })();

        $this->setHeaders($headers);
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
    public function setHeader(
        string $header, 
        string|array $value
    ): void
    {
        $normalized = \strtolower($header);

        if (isset($this->headerNames[$normalized])) {
            unset($this->headers[$this->headerNames[$normalized]]);
        }

        $this->headerNames[$normalized] = $header;

        $this->headers[$header] = \is_string($value) ? [$value] : $value;
    }

    /**
     * @param array<string,string|string[]> $headers
     */
    public function setHeaders(
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

    public function setBody(
        StreamInterface $body
    ): void
    {
        if ($body === $this->stream) {
            return;
        }

        $this->stream = $body;
    }

    public function statusCode(): int
    {
        return $this->statusCode;
    }

    public function setStatus(
        int $code, 
        string $reasonPhrase = ''
    ): void
    {
        $this->statusCode = (int) $code;

        if ($reasonPhrase === '' && isset(Config()->appHttpPhrases()[$this->statusCode])) {
            $reasonPhrase = Config()->appHttpPhrases()[$this->statusCode];
        }

        $this->reasonPhrase = $reasonPhrase;
    }

    public function reasonPhrase(): string
    {
        return $this->reasonPhrase;
    }

    /**
     * Serialization Helper
     * @see https://github.com/walkor/psr7/commit/8f163224ed5bb93fb210da9211651fcd88acb97b#diff-fe65dcdace9cc44252b537bee79dd574edd1bccf6cee646cc860006a6ec50e8b
     */
    public function __toString(): string
    {
        $msg = 'HTTP/'
            . $this->protocolVersion . ' '
            . $this->statusCode . ' '
            . $this->reasonPhrase;

        $headers = $this->headers();

        if (empty($headers)) {
            $msg .= "\r\nContent-Length: " . $this->body()->getSize() .
                "\r\nContent-Type: text/html; charset=utf-8" .
                "\r\nConnection: keep-alive";
        } else {

            if ('' === $this->headerLine('Transfer-Encoding') &&
                '' === $this->headerLine('Content-Length'))
            {
                $msg .= "\r\nContent-Length: " . $this->body()->getSize();
            }

            if ('' === $this->headerLine('Content-Type')) {
                $msg .= "\r\nContent-Type: text/html; charset=utf-8";
            }

            if ('' === $this->headerLine('Connection')) {
                $msg .= "\r\nConnection: keep-alive";
            }

            foreach ($headers as $name => $values) {
                $msg .= "\r\n" . $name . ": " . \implode(', ', $values);
            }
        }

        return $msg . "\r\n\r\n" . $this->body();
    }
}
