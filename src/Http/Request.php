<?php

declare(strict_types=1);

namespace App\Http;

class Request
{
    private string $method;

    private string $path;

    /** @var array<string, string> */
    private array $query;

    /** @var array<string, mixed> */
    private array $jsonBody;

    private string $rawBody;

    /** @param array<string, string> $query */
    private function __construct(string $method, string $path, array $query, array $jsonBody, string $rawBody)
    {
        $this->method = strtoupper($method);
        $this->path = $path;
        $this->query = $query;
        $this->jsonBody = $jsonBody;
        $this->rawBody = $rawBody;
    }

    public static function fromGlobals(array $server, string $rawBody): self
    {
        $method = $server['REQUEST_METHOD'] ?? 'GET';
        $uri = $server['REQUEST_URI'] ?? '/';
        $path = strtok($uri, '?') ?: '/';
        parse_str($server['QUERY_STRING'] ?? '', $query);

        $json = [];
        if ($rawBody !== '') {
            $decoded = json_decode($rawBody, true);
            if (is_array($decoded)) {
                $json = $decoded;
            }
        }

        /** @var array<string, string> $query */
        return new self($method, $path, $query, $json, $rawBody);
    }

    public function method(): string
    {
        return $this->method;
    }

    public function path(): string
    {
        return $this->path;
    }

    /** @return array<string, string> */
    public function query(): array
    {
        return $this->query;
    }

    /** @return array<string, mixed> */
    public function json(): array
    {
        return $this->jsonBody;
    }

    public function rawBody(): string
    {
        return $this->rawBody;
    }
}
