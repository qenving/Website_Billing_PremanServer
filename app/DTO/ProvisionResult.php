<?php

namespace App\DTO;

class ProvisionResult
{
    public function __construct(
        public readonly bool $success,
        public readonly ?string $externalId = null, // ID dari panel eksternal
        public readonly ?array $credentials = null, // username, password, keys, etc
        public readonly ?array $accessInfo = null, // IP addresses, URLs, ports, etc
        public readonly ?string $errorMessage = null,
        public readonly ?array $rawResponse = null
    ) {
    }

    public static function success(
        string $externalId,
        ?array $credentials = null,
        ?array $accessInfo = null,
        ?array $rawResponse = null
    ): self {
        return new self(
            success: true,
            externalId: $externalId,
            credentials: $credentials,
            accessInfo: $accessInfo,
            rawResponse: $rawResponse
        );
    }

    public static function failed(string $errorMessage, ?array $rawResponse = null): self
    {
        return new self(
            success: false,
            errorMessage: $errorMessage,
            rawResponse: $rawResponse
        );
    }

    public function toArray(): array
    {
        return [
            'success' => $this->success,
            'external_id' => $this->externalId,
            'credentials' => $this->credentials,
            'access_info' => $this->accessInfo,
            'error_message' => $this->errorMessage,
            'raw_response' => $this->rawResponse,
        ];
    }

    public function getProvisioningData(): array
    {
        return array_filter([
            'external_id' => $this->externalId,
            'credentials' => $this->credentials,
            'access_info' => $this->accessInfo,
        ]);
    }
}
