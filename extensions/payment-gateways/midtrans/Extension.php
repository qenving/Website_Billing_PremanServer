<?php

namespace Extensions\PaymentGateways\Midtrans;

use App\Extensions\Extension;
use App\Extensions\Contracts\PaymentGatewayInterface;
use App\Models\Invoice;
use App\Models\Payment;

class Extension extends \App\Extensions\Extension implements PaymentGatewayInterface
{
    public function getName(): string
    {
        return 'Midtrans Payment Gateway';
    }

    public function getDescription(): string
    {
        return 'Accept payments via Midtrans (Indonesian payment gateway)';
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
        return 'midtrans-gateway';
    }

    public function getGatewayId(): string
    {
        return 'midtrans';
    }

    public function getDisplayName(): string
    {
        return 'Midtrans';
    }

    public function getLogo(): ?string
    {
        return $this->asset('logo.png');
    }

    public function getConfigFields(): array
    {
        return [
            [
                'name' => 'server_key',
                'label' => 'Server Key',
                'type' => 'password',
                'required' => true,
                'description' => 'Your Midtrans Server Key',
            ],
            [
                'name' => 'client_key',
                'label' => 'Client Key',
                'type' => 'text',
                'required' => true,
                'description' => 'Your Midtrans Client Key',
            ],
            [
                'name' => 'merchant_id',
                'label' => 'Merchant ID',
                'type' => 'text',
                'required' => true,
                'description' => 'Your Midtrans Merchant ID',
            ],
            [
                'name' => 'production',
                'label' => 'Production Mode',
                'type' => 'boolean',
                'default' => false,
                'description' => 'Enable production mode',
            ],
        ];
    }

    public function processPayment(Invoice $invoice, array $paymentData): array
    {
        try {
            // TODO: Implement Midtrans payment processing
            // This is a placeholder implementation

            return [
                'status' => 'pending',
                'transaction_id' => 'midtrans_' . uniqid(),
                'message' => 'Payment initiated via Midtrans',
                'redirect_url' => route('payment.midtrans.confirm', ['invoice' => $invoice->id]),
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
            // TODO: Implement Midtrans webhook handling
            return null;

        } catch (\Exception $e) {
            \Log::error('Midtrans webhook error: ' . $e->getMessage());
            return null;
        }
    }

    public function refund(Payment $payment, float $amount): array
    {
        try {
            // TODO: Implement Midtrans refund

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
        return !empty($this->config['server_key']) && !empty($this->config['client_key']);
    }

    public function getFees(): array
    {
        return [
            'type' => 'percentage',
            'amount' => 2.0,
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
