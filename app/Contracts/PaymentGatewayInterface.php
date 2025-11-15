<?php

namespace App\Contracts;

use App\DTO\HealthCheckResult;
use App\DTO\PaymentCallbackResult;
use App\Models\Client;
use App\Models\Invoice;
use App\Models\Payment;
use Illuminate\Http\Request;

interface PaymentGatewayInterface
{
    /**
     * Create payment invoice/transaction di gateway
     *
     * @param Invoice $invoice
     * @param Client $client
     * @return array ['payment_url' => string, 'transaction_id' => string, 'expires_at' => datetime]
     */
    public function createInvoice(Invoice $invoice, Client $client): array;

    /**
     * Get payment status dari gateway
     *
     * @param string $referenceId Transaction ID dari gateway
     * @return string Status: pending|completed|failed|expired
     */
    public function getPaymentStatus(string $referenceId): string;

    /**
     * Handle callback/webhook dari gateway
     *
     * @param Request $request
     * @return PaymentCallbackResult
     */
    public function handleCallback(Request $request): PaymentCallbackResult;

    /**
     * Refund payment
     *
     * @param Payment $payment
     * @param float $amount
     * @param string|null $reason
     * @return bool
     */
    public function refundPayment(Payment $payment, float $amount, ?string $reason = null): bool;

    /**
     * Get configuration schema untuk form admin
     *
     * @return array Format: ['field_name' => ['type' => 'text', 'label' => 'Label', 'required' => true]]
     */
    public function getConfigSchema(): array;

    /**
     * Validate configuration
     *
     * @param array $config
     * @return bool
     */
    public function validateConfig(array $config): bool;

    /**
     * Health check ke gateway API
     *
     * @return HealthCheckResult
     */
    public function healthCheck(): HealthCheckResult;

    /**
     * Get callback/webhook URL untuk gateway ini
     *
     * @return string
     */
    public function getCallbackUrl(): string;
}
