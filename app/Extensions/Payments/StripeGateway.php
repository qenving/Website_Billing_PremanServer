<?php

namespace App\Extensions\Payments;

use App\Contracts\PaymentGatewayInterface;
use App\DTO\HealthCheckResult;
use App\DTO\PaymentCallbackResult;
use App\Models\Client;
use App\Models\Invoice;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class StripeGateway implements PaymentGatewayInterface
{
    protected array $config;
    protected string $baseUrl = 'https://api.stripe.com/v1';

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    public function createInvoice(Invoice $invoice, Client $client): array
    {
        try {
            $secretKey = $this->config['secret_key'];

            // Create a Stripe Checkout Session
            $response = Http::withBasicAuth($secretKey, '')
                ->asForm()
                ->post($this->baseUrl . '/checkout/sessions', [
                    'payment_method_types[]' => 'card',
                    'line_items[0][price_data][currency]' => strtolower($invoice->currency),
                    'line_items[0][price_data][product_data][name]' => 'Invoice ' . $invoice->invoice_number,
                    'line_items[0][price_data][unit_amount]' => (int) ($invoice->total * 100), // Convert to cents
                    'line_items[0][quantity]' => 1,
                    'mode' => 'payment',
                    'success_url' => route('client.invoices.show', $invoice->id) . '?stripe_success=1&session_id={CHECKOUT_SESSION_ID}',
                    'cancel_url' => route('client.invoices.show', $invoice->id) . '?stripe_cancel=1',
                    'client_reference_id' => $invoice->invoice_number,
                    'customer_email' => $client->user->email,
                    'expires_at' => now()->addHours(24)->timestamp,
                ]);

            if ($response->successful()) {
                $session = $response->json();
                return [
                    'payment_url' => $session['url'],
                    'transaction_id' => $session['id'],
                    'expires_at' => now()->addHours(24),
                ];
            }

            throw new \Exception('Failed to create Stripe session: ' . $response->json('error.message'));
        } catch (\Exception $e) {
            Log::error('Stripe createInvoice error', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    public function getPaymentStatus(string $referenceId): string
    {
        try {
            $secretKey = $this->config['secret_key'];

            $response = Http::withBasicAuth($secretKey, '')
                ->get($this->baseUrl . '/checkout/sessions/' . $referenceId);

            if ($response->successful()) {
                $status = $response->json('payment_status');
                return match($status) {
                    'paid' => 'completed',
                    'unpaid' => 'pending',
                    'no_payment_required' => 'completed',
                    default => 'pending',
                };
            }

            return 'pending';
        } catch (\Exception $e) {
            Log::error('Stripe getPaymentStatus error', ['error' => $e->getMessage()]);
            return 'pending';
        }
    }

    public function handleCallback(Request $request): PaymentCallbackResult
    {
        $result = new PaymentCallbackResult();

        try {
            $payload = $request->getContent();
            $sigHeader = $request->header('Stripe-Signature');
            $webhookSecret = $this->config['webhook_secret'] ?? '';

            // Verify webhook signature
            if (!empty($webhookSecret)) {
                $this->verifyWebhookSignature($payload, $sigHeader, $webhookSecret);
            }

            $event = json_decode($payload, true);

            if ($event['type'] === 'checkout.session.completed') {
                $session = $event['data']['object'];
                $result->transactionId = $session['client_reference_id'];
                $result->amount = $session['amount_total'] / 100; // Convert from cents
                $result->success = true;
                $result->status = 'completed';
            } else {
                $result->success = false;
                $result->status = 'pending';
            }

            $result->rawPayload = $event;
        } catch (\Exception $e) {
            Log::error('Stripe handleCallback error', ['error' => $e->getMessage()]);
            $result->success = false;
            $result->status = 'failed';
            $result->errorMessage = $e->getMessage();
        }

        return $result;
    }

    protected function verifyWebhookSignature(string $payload, string $sigHeader, string $secret): void
    {
        $tolerance = 300; // 5 minutes
        $elements = explode(',', $sigHeader);
        $timestamp = null;
        $signatures = [];

        foreach ($elements as $element) {
            [$key, $value] = explode('=', $element, 2);
            if ($key === 't') {
                $timestamp = $value;
            } elseif ($key === 'v1') {
                $signatures[] = $value;
            }
        }

        if (empty($timestamp) || empty($signatures)) {
            throw new \Exception('Invalid signature header');
        }

        if (abs(time() - $timestamp) > $tolerance) {
            throw new \Exception('Timestamp outside tolerance');
        }

        $expectedSignature = hash_hmac('sha256', $timestamp . '.' . $payload, $secret);

        $signatureFound = false;
        foreach ($signatures as $signature) {
            if (hash_equals($expectedSignature, $signature)) {
                $signatureFound = true;
                break;
            }
        }

        if (!$signatureFound) {
            throw new \Exception('Invalid signature');
        }
    }

    public function refundPayment(Payment $payment, float $amount, ?string $reason = null): bool
    {
        try {
            $secretKey = $this->config['secret_key'];
            $paymentIntentId = $payment->transaction_id;

            $response = Http::withBasicAuth($secretKey, '')
                ->asForm()
                ->post($this->baseUrl . '/refunds', [
                    'payment_intent' => $paymentIntentId,
                    'amount' => (int) ($amount * 100), // Convert to cents
                    'reason' => 'requested_by_customer',
                ]);

            return $response->successful();
        } catch (\Exception $e) {
            Log::error('Stripe refund error', ['error' => $e->getMessage()]);
            return false;
        }
    }

    public function getConfigSchema(): array
    {
        return [
            'publishable_key' => [
                'type' => 'text',
                'label' => 'Publishable Key',
                'required' => true,
                'description' => 'Your Stripe publishable key',
            ],
            'secret_key' => [
                'type' => 'password',
                'label' => 'Secret Key',
                'required' => true,
                'description' => 'Your Stripe secret key',
            ],
            'webhook_secret' => [
                'type' => 'password',
                'label' => 'Webhook Secret',
                'required' => false,
                'description' => 'Your Stripe webhook signing secret (optional but recommended)',
            ],
        ];
    }

    public function validateConfig(array $config): bool
    {
        return !empty($config['publishable_key']) && !empty($config['secret_key']);
    }

    public function healthCheck(): HealthCheckResult
    {
        $result = new HealthCheckResult();
        $result->serviceName = 'Stripe Payment Gateway';

        try {
            $secretKey = $this->config['secret_key'];

            $response = Http::timeout(10)
                ->withBasicAuth($secretKey, '')
                ->get($this->baseUrl . '/balance');

            if ($response->successful()) {
                $result->status = 'ok';
                $result->message = 'Connection successful';
            } else {
                $result->status = 'error';
                $result->message = 'Failed to connect: ' . $response->json('error.message');
            }
        } catch (\Exception $e) {
            $result->status = 'error';
            $result->message = 'Connection error: ' . $e->getMessage();
        }

        $result->checkedAt = now();
        return $result;
    }

    public function getCallbackUrl(): string
    {
        return route('payment.callback', ['gateway' => 'stripe']);
    }
}
