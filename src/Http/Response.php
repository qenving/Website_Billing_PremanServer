<?php

declare(strict_types=1);

namespace App\Http;

class Response
{
    private int $statusCode;

    /** @var array<string, string> */
    private array $headers = [];

    private string $body;

    private function __construct(int $statusCode, array $headers, string $body)
    {
        $this->statusCode = $statusCode;
        $this->headers = $headers;
        $this->body = $body;
    }

    public static function json(array $data, int $statusCode = 200): self
    {
        return new self($statusCode, ['Content-Type' => 'application/json'], json_encode($data, JSON_PRETTY_PRINT));
    }

    public static function html(string $content, int $statusCode = 200): self
    {
        return new self($statusCode, ['Content-Type' => 'text/html; charset=utf-8'], $content);
    }

    public static function plain(string $content, int $statusCode = 200): self
    {
        return new self($statusCode, ['Content-Type' => 'text/plain; charset=utf-8'], $content);
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    /** @return array<string, string> */
    public function getHeaders(): array
    {
        return $this->headers;
    }

    public function getBody(): string
    {
        return $this->body;
    }
}
