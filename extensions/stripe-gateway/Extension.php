<?php

namespace Extensions\StripeGateway;

use App\Extensions\Extension;
use App\Extensions\Contracts\PaymentGatewayInterface;
use App\Models\Invoice;
use App\Models\Payment;

class Extension extends \App\Extensions\Extension implements PaymentGatewayInterface
{
    public function getName(): string
    {
        return 'Stripe Payment Gateway';
    }

    public function getDescription(): string
    {
        return 'Accept credit card payments via Stripe';
    }

    public function getVersion(): string
    {
        return '1.0.0';
    }

    public function getAuthor(): string
    {
        return 'Your Company';
    }

    public function getExtensionId(): string
    {
        return 'stripe-gateway';
    }

    public function getGatewayId(): string
    {
        return 'stripe';
    }

    public function getDisplayName(): string
    {
        return 'Stripe';
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
                'description' => 'Your Stripe Secret Key',
            ],
            [
                'name' => 'publishable_key',
                'label' => 'Publishable Key',
                'type' => 'text',
                'required' => true,
                'description' => 'Your Stripe Publishable Key',
            ],
            [
                'name' => 'webhook_secret',
                'label' => 'Webhook Secret',
                'type' => 'password',
                'required' => false,
                'description' => 'Webhook signing secret (optional)',
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
            // Initialize Stripe
            \Stripe\Stripe::setApiKey($this->config['secret_key']);

            // Create payment intent
            $intent = \Stripe\PaymentIntent::create([
                'amount' => $invoice->total * 100, // Stripe uses cents
                'currency' => strtolower(config('app.currency', 'usd')),
                'description' => 'Invoice #' . $invoice->invoice_number,
                'metadata' => [
                    'invoice_id' => $invoice->id,
                    'client_id' => $invoice->client_id,
                ],
            ]);

            return [
                'status' => 'pending',
                'transaction_id' => $intent->id,
                'message' => 'Payment initiated',
                'redirect_url' => route('payment.stripe.confirm', ['intent' => $intent->id]),
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
            \Stripe\Stripe::setApiKey($this->config['secret_key']);

            // Verify webhook signature
            if (isset($this->config['webhook_secret'])) {
                $event = \Stripe\Webhook::constructEvent(
                    $data['payload'],
                    $data['signature'],
                    $this->config['webhook_secret']
                );
            } else {
                $event = $data['event'];
            }

            // Handle payment intent succeeded
            if ($event->type === 'payment_intent.succeeded') {
                $paymentIntent = $event->data->object;

                $invoice = Invoice::find($paymentIntent->metadata->invoice_id);

                if ($invoice) {
                    $payment = Payment::create([
                        'invoice_id' => $invoice->id,
                        'amount' => $paymentIntent->amount / 100,
                        'gateway' => 'stripe',
                        'transaction_id' => $paymentIntent->id,
                        'status' => 'completed',
                    ]);

                    return $payment;
                }
            }

            return null;

        } catch (\Exception $e) {
            \Log::error('Stripe webhook error: ' . $e->getMessage());
            return null;
        }
    }

    public function refund(Payment $payment, float $amount): array
    {
        try {
            \Stripe\Stripe::setApiKey($this->config['secret_key']);

            $refund = \Stripe\Refund::create([
                'payment_intent' => $payment->transaction_id,
                'amount' => $amount * 100,
            ]);

            return [
                'status' => 'success',
                'message' => 'Refund processed successfully',
                'refund_id' => $refund->id,
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
        return ['credit_card', 'debit_card'];
    }

    public function isConfigured(): bool
    {
        return !empty($this->config['secret_key']) && !empty($this->config['publishable_key']);
    }

    public function getFees(): array
    {
        return [
            'type' => 'percentage',
            'amount' => 2.9,
            'fixed' => 0.30,
        ];
    }

    public function boot(): void
    {
        // Register routes
        if (file_exists(__DIR__ . '/routes/web.php')) {
            require __DIR__ . '/routes/web.php';
        }
    }
}
