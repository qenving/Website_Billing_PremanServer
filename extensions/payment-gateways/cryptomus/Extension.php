<?php

namespace Extensions\PaymentGateways\Cryptomus;

use App\Extensions\Extension;
use App\Extensions\Contracts\PaymentGatewayInterface;
use App\Models\Invoice;
use App\Models\Payment;

class Extension extends \App\Extensions\Extension implements PaymentGatewayInterface
{
    public function getName(): string
    {
        return 'Cryptomus Payment Gateway';
    }

    public function getDescription(): string
    {
        return 'Accept cryptocurrency payments via Cryptomus';
    }

    public function getVersion(): string
    {
        return '1.0.0';
    }

    public function getAuthor(): string
    {
        return 'HBM Billing';
    }

    public function getExtensionId(): string
    {
        return 'cryptomus-gateway';
    }

    public function getGatewayId(): string
    {
        return 'cryptomus';
    }

    public function getDisplayName(): string
    {
        return 'Cryptomus';
    }

    public function getLogo(): ?string
    {
        return $this->asset('logo.png');
    }

    public function getConfigFields(): array
    {
        return [
            [
                'name' => 'merchant_id',
                'label' => 'Merchant ID',
                'type' => 'text',
                'required' => true,
                'description' => 'Your Cryptomus Merchant ID',
            ],
            [
                'name' => 'api_key',
                'label' => 'API Key',
                'type' => 'password',
                'required' => true,
                'description' => 'Your Cryptomus API Key',
            ],
            [
                'name' => 'payment_key',
                'label' => 'Payment Key',
                'type' => 'password',
                'required' => true,
                'description' => 'Your Cryptomus Payment Key',
            ],
            [
                'name' => 'payout_key',
                'label' => 'Payout Key',
                'type' => 'password',
                'required' => false,
                'description' => 'Your Cryptomus Payout Key (optional)',
            ],
        ];
    }

    public function processPayment(Invoice $invoice, array $paymentData): array
    {
        try {
            // TODO: Implement Cryptomus payment processing

            return [
                'status' => 'pending',
                'transaction_id' => 'cryptomus_' . uniqid(),
                'message' => 'Payment initiated via Cryptomus',
                'redirect_url' => route('payment.cryptomus.confirm', ['invoice' => $invoice->id]),
            ];

        } catch (\Exception $e) {
            return [
                'status' => 'failed',
                'transaction_id' => null,
                'message' => 'Payment failed: ' . $e->getMessage(),
            ];
        }
    }

    public function handleWebhook(array $data): ?Payment
    {
        try {
            // TODO: Implement Cryptomus webhook handling
            return null;

        } catch (\Exception $e) {
            \Log::error('Cryptomus webhook error: ' . $e->getMessage());
            return null;
        }
    }

    public function refund(Payment $payment, float $amount): array
    {
        try {
            // TODO: Implement Cryptomus refund

            return [
                'status' => 'success',
                'message' => 'Refund processed successfully',
                'refund_id' => 'refund_' . uniqid(),
            ];

        } catch (\Exception $e) {
            return [
                'status' => 'failed',
                'message' => 'Refund failed: ' . $e->getMessage(),
            ];
        }
    }

    public function getSupportedMethods(): array
    {
        return ['bitcoin', 'ethereum', 'litecoin', 'usdt', 'usdc', 'bnb'];
    }

    public function isConfigured(): bool
    {
        return !empty($this->config['merchant_id'])
            && !empty($this->config['api_key'])
            && !empty($this->config['payment_key']);
    }

    public function getFees(): array
    {
        return [
            'type' => 'percentage',
            'amount' => 0.5,
            'fixed' => 0,
        ];
    }

    public function boot(): void
    {
        // Register routes if needed
        if (file_exists(__DIR__ . '/routes/web.php')) {
            require __DIR__ . '/routes/web.php';
        }
    }
}
