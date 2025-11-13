<?php

namespace App\DTO;

class HealthCheckResult
{
    public function __construct(
        public readonly string $status, // ok|warning|error
        public readonly ?string $message = null,
        public readonly ?array $details = null
    ) {
    }

    public static function ok(?string $message = 'Connection successful'): self
    {
        return new self('ok', $message);
    }

    public static function warning(string $message, ?array $details = null): self
    {
        return new self('warning', $message, $details);
    }

    public static function error(string $message, ?array $details = null): self
    {
        return new self('error', $message, $details);
    }

    public function isOk(): bool
    {
        return $this->status === 'ok';
    }

    public function isError(): bool
    {
        return $this->status === 'error';
    }

    public function toArray(): array
    {
        return [
            'status' => $this->status,
            'message' => $this->message,
            'details' => $this->details,
        ];
    }
}
