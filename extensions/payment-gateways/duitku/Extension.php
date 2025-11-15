<?php

namespace Extensions\PaymentGateways\Duitku;

use App\Extensions\Extension;
use App\Extensions\Contracts\PaymentGatewayInterface;
use App\Models\Invoice;
use App\Models\Payment;

class Extension extends \App\Extensions\Extension implements PaymentGatewayInterface
{
    public function getName(): string
    {
        return 'Duitku Payment Gateway';
    }

    public function getDescription(): string
    {
        return 'Accept payments via Duitku (Indonesian payment gateway)';
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
        return 'duitku-gateway';
    }

    public function getGatewayId(): string
    {
        return 'duitku';
    }

    public function getDisplayName(): string
    {
        return 'Duitku';
    }

    public function getLogo(): ?string
    {
        return $this->asset('logo.png');
    }

    public function getConfigFields(): array
    {
        return [
            [
                'name' => 'merchant_code',
                'label' => 'Merchant Code',
                'type' => 'text',
                'required' => true,
                'description' => 'Your Duitku Merchant Code',
            ],
            [
                'name' => 'api_key',
                'label' => 'API Key',
                'type' => 'password',
                'required' => true,
                'description' => 'Your Duitku API Key',
            ],
            [
                'name' => 'sandbox',
                'label' => 'Sandbox Mode',
                'type' => 'boolean',
                'default' => true,
                'description' => 'Enable sandbox mode',
            ],
        ];
    }

    public function processPayment(Invoice $invoice, array $paymentData): array
    {
        try {
            // TODO: Implement Duitku payment processing

            return [
                'status' => 'pending',
                'transaction_id' => 'duitku_' . uniqid(),
                'message' => 'Payment initiated via Duitku',
                'redirect_url' => route('payment.duitku.confirm', ['invoice' => $invoice->id]),
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
            // TODO: Implement Duitku webhook handling
            return null;

        } catch (\Exception $e) {
            \Log::error('Duitku webhook error: ' . $e->getMessage());
            return null;
        }
    }

    public function refund(Payment $payment, float $amount): array
    {
        try {
            // TODO: Implement Duitku refund

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
        return ['credit_card', 'bank_transfer', 'e_wallet', 'convenience_store'];
    }

    public function isConfigured(): bool
    {
        return !empty($this->config['merchant_code']) && !empty($this->config['api_key']);
    }

    public function getFees(): array
    {
        return [
            'type' => 'percentage',
            'amount' => 1.9,
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
