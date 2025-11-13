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

class XenditGateway implements PaymentGatewayInterface
{
    protected array $config;
    protected string $baseUrl = 'https://api.xendit.co';

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    public function createInvoice(Invoice $invoice, Client $client): array
    {
        $payload = [
            'external_id' => $invoice->invoice_number,
            'amount' => (float) $invoice->total,
            'payer_email' => $client->user->email,
            'description' => "Payment for invoice {$invoice->invoice_number}",
            'invoice_duration' => 86400, // 24 hours
            'success_redirect_url' => route('client.invoices.show', $invoice->id),
            'failure_redirect_url' => route('client.invoices.show', $invoice->id),
            'currency' => $invoice->currency,
            'items' => $invoice->items->map(function ($item) {
                return [
                    'name' => $item->description,
                    'quantity' => $item->quantity,
                    'price' => (float) $item->unit_price,
                ];
            })->toArray(),
            'customer' => [
                'given_names' => $client->user->name,
                'email' => $client->user->email,
                'mobile_number' => $client->phone,
            ],
        ];

        try {
            $response = Http::withBasicAuth($this->config['secret_key'], '')
                ->post("{$this->baseUrl}/v2/invoices", $payload);

            if ($response->successful()) {
                $data = $response->json();

                return [
                    'payment_url' => $data['invoice_url'] ?? '',
                    'transaction_id' => $data['id'] ?? '',
                    'expires_at' => $data['expiry_date'] ?? now()->addHours(24),
                ];
            }

            Log::error('Xendit createInvoice failed', [
                'response' => $response->json(),
            ]);

            throw new \Exception('Failed to create Xendit invoice');
        } catch (\Exception $e) {
            Log::error('Xendit createInvoice exception', [
                'message' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    public function getPaymentStatus(string $referenceId): string
    {
        try {
            $response = Http::withBasicAuth($this->config['secret_key'], '')
                ->get("{$this->baseUrl}/v2/invoices/{$referenceId}");

            if ($response->successful()) {
                $data = $response->json();
                $status = $data['status'] ?? 'PENDING';

                return match ($status) {
                    'PAID', 'SETTLED' => 'completed',
                    'PENDING' => 'pending',
                    'EXPIRED' => 'expired',
                    default => 'pending',
                };
            }

            return 'pending';
        } catch (\Exception $e) {
            Log::error('Xendit getPaymentStatus exception', [
                'reference_id' => $referenceId,
                'message' => $e->getMessage(),
            ]);

            return 'pending';
        }
    }

    public function handleCallback(Request $request): PaymentCallbackResult
    {
        $rawData = $request->all();

        // Verify callback token
        $callbackToken = $request->header('x-callback-token');

        if ($callbackToken !== ($this->config['callback_token'] ?? '')) {
            return PaymentCallbackResult::invalid('Invalid callback token', $rawData);
        }

        $status = match ($rawData['status'] ?? '') {
            'PAID', 'SETTLED' => 'completed',
            'PENDING' => 'pending',
            'EXPIRED' => 'expired',
            default => 'pending',
        };

        return PaymentCallbackResult::success(
            transactionId: $rawData['id'] ?? '',
            status: $status,
            amount: (float) ($rawData['amount'] ?? 0),
            currency: $rawData['currency'] ?? 'IDR',
            rawData: $rawData
        );
    }

    public function refundPayment(Payment $payment, float $amount, ?string $reason = null): bool
    {
        try {
            $payload = [
                'invoice_id' => $payment->transaction_id,
                'reason' => $reason ?? 'Refund request',
                'amount' => (float) $amount,
            ];

            $response = Http::withBasicAuth($this->config['secret_key'], '')
                ->post("{$this->baseUrl}/v2/invoices/{$payment->transaction_id}/refund", $payload);

            return $response->successful();
        } catch (\Exception $e) {
            Log::error('Xendit refund exception', [
                'payment_id' => $payment->id,
                'message' => $e->getMessage(),
            ]);

            return false;
        }
    }

    public function getConfigSchema(): array
    {
        return [
            'secret_key' => [
                'type' => 'text',
                'label' => 'Secret Key',
                'required' => true,
                'encrypted' => true,
            ],
            'public_key' => [
                'type' => 'text',
                'label' => 'Public Key',
                'required' => true,
            ],
            'callback_token' => [
                'type' => 'text',
                'label' => 'Callback Verification Token',
                'required' => true,
                'encrypted' => true,
                'help' => 'Generate token dari Xendit Dashboard',
            ],
        ];
    }

    public function validateConfig(array $config): bool
    {
        return !empty($config['secret_key'])
            && !empty($config['public_key'])
            && !empty($config['callback_token']);
    }

    public function healthCheck(): HealthCheckResult
    {
        try {
            // Test dengan get balance
            $response = Http::withBasicAuth($this->config['secret_key'], '')
                ->get("{$this->baseUrl}/balance");

            if ($response->successful()) {
                return HealthCheckResult::ok('Xendit API is reachable');
            }

            if ($response->status() === 401) {
                return HealthCheckResult::error('Invalid API credentials');
            }

            return HealthCheckResult::error('Failed to connect to Xendit API', [
                'status_code' => $response->status(),
            ]);
        } catch (\Exception $e) {
            return HealthCheckResult::error('Xendit connection error: ' . $e->getMessage());
        }
    }

    public function getCallbackUrl(): string
    {
        return route('payment.callback', ['gateway' => 'xendit']);
    }
}
