<?php

namespace App\Extensions\Contracts;

use App\Models\Invoice;
use App\Models\Payment;

interface PaymentGatewayInterface extends ExtensionInterface
{
    /**
     * Get gateway unique identifier
     */
    public function getGatewayId(): string;

    /**
     * Get gateway display name
     */
    public function getDisplayName(): string;

    /**
     * Get gateway logo URL
     */
    public function getLogo(): ?string;

    /**
     * Get configuration fields for admin settings
     * Returns array of field definitions
     */
    public function getConfigFields(): array;

    /**
     * Process payment
     * Returns array with:
     * - status: 'success', 'pending', or 'failed'
     * - transaction_id: Gateway transaction ID
     * - message: Status message
     * - redirect_url: Optional redirect URL for customer
     */
    public function processPayment(Invoice $invoice, array $paymentData): array;

    /**
     * Handle webhook/callback from payment gateway
     * Returns Payment model or null
     */
    public function handleWebhook(array $data): ?Payment;

    /**
     * Refund payment
     * Returns array with status and message
     */
    public function refund(Payment $payment, float $amount): array;

    /**
     * Get payment methods supported by this gateway
     * e.g., ['credit_card', 'bank_transfer', 'e-wallet']
     */
    public function getSupportedMethods(): array;

    /**
     * Check if gateway is configured properly
     */
    public function isConfigured(): bool;

    /**
     * Get gateway fees (percentage or fixed)
     * Returns ['type' => 'percentage|fixed', 'amount' => 2.5]
     */
    public function getFees(): array;
}
