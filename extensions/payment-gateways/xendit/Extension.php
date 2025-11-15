<?php

namespace Extensions\PaymentGateways\Xendit;

use App\Extensions\Extension;
use App\Extensions\Contracts\PaymentGatewayInterface;
use App\Models\Invoice;
use App\Models\Payment;

class Extension extends \App\Extensions\Extension implements PaymentGatewayInterface
{
    public function getName(): string
    {
        return 'Xendit Payment Gateway';
    }

    public function getDescription(): string
    {
        return 'Accept payments via Xendit (Indonesian payment gateway)';
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
        return 'xendit-gateway';
    }

    public function getGatewayId(): string
    {
        return 'xendit';
    }

    public function getDisplayName(): string
    {
        return 'Xendit';
    }

    public function getLogo(): ?string
    {
        return $this->asset('logo.png');
    }

    public function getConfigFields(): array
    {
        return [
            [
                'name' => 'secret_key',
                'label' => 'Secret Key',
                'type' => 'password',
                'required' => true,
                'description' => 'Your Xendit Secret Key',
            ],
            [
                'name' => 'public_key',
                'label' => 'Public Key',
                'type' => 'text',
                'required' => true,
                'description' => 'Your Xendit Public Key',
            ],
            [
                'name' => 'webhook_token',
                'label' => 'Webhook Verification Token',
                'type' => 'password',
                'required' => false,
                'description' => 'Webhook verification token (optional)',
            ],
            [
                'name' => 'test_mode',
                'label' => 'Test Mode',
                'type' => 'boolean',
                'default' => true,
                'description' => 'Enable test mode',
            ],
        ];
    }

    public function processPayment(Invoice $invoice, array $paymentData): array
    {
        try {
            // TODO: Implement Xendit payment processing

            return [
                'status' => 'pending',
                'transaction_id' => 'xendit_' . uniqid(),
                'message' => 'Payment initiated via Xendit',
                'redirect_url' => route('payment.xendit.confirm', ['invoice' => $invoice->id]),
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
            // TODO: Implement Xendit webhook handling
            return null;

        } catch (\Exception $e) {
            \Log::error('Xendit webhook error: ' . $e->getMessage());
            return null;
        }
    }

    public function refund(Payment $payment, float $amount): array
    {
        try {
            // TODO: Implement Xendit refund

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
        return ['credit_card', 'e_wallet', 'virtual_account', 'retail_outlet'];
    }

    public function isConfigured(): bool
    {
        return !empty($this->config['secret_key']) && !empty($this->config['public_key']);
    }

    public function getFees(): array
    {
        return [
            'type' => 'percentage',
            'amount' => 2.9,
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
