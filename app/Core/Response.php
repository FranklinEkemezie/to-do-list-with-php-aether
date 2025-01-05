<?php

declare(strict_types=1);

namespace FranklinEkemezie\PHPAether\Core;

/**
 * Response class
 * 
 * Represents a HTTP response
 */
class Response
{

    private int $statusCode;
    private string $reasonPhrase;
    private string $body;
    private array $headers;
    private array $cookies;

    public function __construct(
        int $statusCode=200,
        ?string $reasonPhrase=null,
        ?string $responseType=null,
        ?string $body=null,
        ?array $headers=[]
    )
    {
        $defaultReasonPhrase = self::getDefaultReasonPhrase($statusCode);
        if ($defaultReasonPhrase === 'Unknown Status') {
            throw new \InvalidArgumentException("Unknown status code: $statusCode");
        }

        $this->statusCode = $statusCode;
        $this->reasonPhrase = $reasonPhrase ?? $defaultReasonPhrase;
        $this->body = $body ?? '';

        $defaultHeaders = [
            "Content-Type" => $responseType ?? 'application/json',
            "Access-Control-Allow-Origin" => '*'
        ];

        $this->headers = array_merge($headers, $defaultHeaders);
        $this->cookies = [];
    }

    private static function getDefaultReasonPhrase(int $statusCode): string
    {
        return match($statusCode) {
            200 => 'OK',
            201 => 'Created',
            204 => 'No Content',
            400 => 'Bad Request',
            401 => 'Unauthorized',
            403 => 'Forbidden',
            404 => 'Not Found',
            405 => 'Method Not Allowed',
            500 => 'Internal Server Error',
            503 => 'Service Unvailable',
            default => 'Unknown Status'
        };
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    public function getReasonPhrase(): string
    {
        return $this->reasonPhrase;
    }

    public function setHeader(string $key, string $value): self
    {
        $this->headers[$key] = $value;
        return $this;
    }

    public function getHeaders(): array
    {
        return $this->headers;
    }

    public function getHeader(string $key): string
    {
        return $this->headers[$key];
    }

    public function setBody(string $body): self
    {
        $this->body = $body;
        return $this;
    }
    
    public function getBody(): string
    {
        return $this->body;
    }

    public function setCookie(string $name, string $value): self
    {
        $this->cookies[$name] = $value;
        return $this;
    }

    public function getCookies(): array
    {
        return $this->cookies;
    }

    public function send(): string
    {
        // Set status line
        header(sprintf('HTTP/1.1 %d %s', $this->statusCode, $this->reasonPhrase));

        // Send headers
        foreach($this->headers as $key => $value) {
            header("$key: $value");
        }

        // Send body
        return (string) $this->body;
    }

    public function __toString(): string
    {
        return $this->send();
    }
}