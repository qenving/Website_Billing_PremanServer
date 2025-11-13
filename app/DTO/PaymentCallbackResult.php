<?php

namespace App\DTO;

class PaymentCallbackResult
{
    public function __construct(
        public readonly bool $isValid, // signature valid atau tidak
        public readonly string $status, // pending|completed|failed|expired
        public readonly string $transactionId,
        public readonly ?float $amount = null,
        public readonly ?string $currency = null,
        public readonly ?array $rawData = null,
        public readonly ?string $errorMessage = null
    ) {
    }

    public static function success(
        string $transactionId,
        string $status,
        float $amount,
        string $currency,
        ?array $rawData = null
    ): self {
        return new self(
            isValid: true,
            status: $status,
            transactionId: $transactionId,
            amount: $amount,
            currency: $currency,
            rawData: $rawData
        );
    }

    public static function invalid(string $errorMessage, ?array $rawData = null): self
    {
        return new self(
            isValid: false,
            status: 'failed',
            transactionId: '',
            errorMessage: $errorMessage,
            rawData: $rawData
        );
    }

    public function isCompleted(): bool
    {
        return $this->isValid && $this->status === 'completed';
    }

    public function isFailed(): bool
    {
        return !$this->isValid || $this->status === 'failed';
    }

    public function toArray(): array
    {
        return [
            'is_valid' => $this->isValid,
            'status' => $this->status,
            'transaction_id' => $this->transactionId,
            'amount' => $this->amount,
            'currency' => $this->currency,
            'raw_data' => $this->rawData,
            'error_message' => $this->errorMessage,
        ];
    }
}
