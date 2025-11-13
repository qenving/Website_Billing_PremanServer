<?php

namespace App\DTO;

class ServiceStatus
{
    public function __construct(
        public readonly string $status, // running|stopped|suspended|error|unknown
        public readonly ?string $message = null,
        public readonly ?array $details = null // CPU usage, RAM, bandwidth, etc
    ) {
    }

    public static function running(?array $details = null): self
    {
        return new self('running', 'Service is running', $details);
    }

    public static function stopped(?string $message = 'Service is stopped'): self
    {
        return new self('stopped', $message);
    }

    public static function suspended(?string $message = 'Service is suspended'): self
    {
        return new self('suspended', $message);
    }

    public static function error(string $message, ?array $details = null): self
    {
        return new self('error', $message, $details);
    }

    public static function unknown(?string $message = 'Status unknown'): self
    {
        return new self('unknown', $message);
    }

    public function isRunning(): bool
    {
        return $this->status === 'running';
    }

    public function isStopped(): bool
    {
        return $this->status === 'stopped';
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
