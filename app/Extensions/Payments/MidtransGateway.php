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

class MidtransGateway implements PaymentGatewayInterface
{
    protected array $config;
    protected string $baseUrl;

    public function __construct(array $config)
    {
        $this->config = $config;
        $this->baseUrl = $config['test_mode'] ?? true
            ? 'https://app.sandbox.midtrans.com'
            : 'https://app.midtrans.com';
    }

    public function createInvoice(Invoice $invoice, Client $client): array
    {
        $payload = [
            'transaction_details' => [
                'order_id' => $invoice->invoice_number,
                'gross_amount' => (int) $invoice->total,
            ],
            'customer_details' => [
                'first_name' => $client->user->name,
                'email' => $client->user->email,
                'phone' => $client->phone,
            ],
            'item_details' => $invoice->items->map(function ($item) {
                return [
                    'id' => $item->id,
                    'price' => (int) $item->unit_price,
                    'quantity' => $item->quantity,
                    'name' => $item->description,
                ];
            })->toArray(),
        ];

        try {
            $response = Http::withBasicAuth($this->config['server_key'], '')
                ->post("{$this->baseUrl}/snap/v1/transactions", $payload);

            if ($response->successful()) {
                $data = $response->json();

                return [
                    'payment_url' => $data['redirect_url'] ?? '',
                    'transaction_id' => $data['token'] ?? '',
                    'expires_at' => now()->addHours(24),
                ];
            }

            Log::error('Midtrans createInvoice failed', [
                'response' => $response->json(),
            ]);

            throw new \Exception('Failed to create Midtrans transaction');
        } catch (\Exception $e) {
            Log::error('Midtrans createInvoice exception', [
                'message' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    public function getPaymentStatus(string $referenceId): string
    {
        try {
            $response = Http::withBasicAuth($this->config['server_key'], '')
                ->get("{$this->baseUrl}/v2/{$referenceId}/status");

            if ($response->successful()) {
                $data = $response->json();
                $status = $data['transaction_status'] ?? 'pending';

                return match ($status) {
                    'capture', 'settlement' => 'completed',
                    'pending' => 'pending',
                    'deny', 'cancel', 'expire' => 'failed',
                    default => 'pending',
                };
            }

            return 'pending';
        } catch (\Exception $e) {
            Log::error('Midtrans getPaymentStatus exception', [
                'reference_id' => $referenceId,
                'message' => $e->getMessage(),
            ]);

            return 'pending';
        }
    }

    public function handleCallback(Request $request): PaymentCallbackResult
    {
        $rawData = $request->all();

        // Verify signature
        $signature = hash('sha512',
            ($rawData['order_id'] ?? '') .
            ($rawData['status_code'] ?? '') .
            ($rawData['gross_amount'] ?? '') .
            $this->config['server_key']
        );

        if ($signature !== ($rawData['signature_key'] ?? '')) {
            return PaymentCallbackResult::invalid('Invalid signature', $rawData);
        }

        $status = match ($rawData['transaction_status'] ?? '') {
            'capture', 'settlement' => 'completed',
            'pending' => 'pending',
            'deny', 'cancel', 'expire' => 'failed',
            default => 'pending',
        };

        return PaymentCallbackResult::success(
            transactionId: $rawData['transaction_id'] ?? '',
            status: $status,
            amount: (float) ($rawData['gross_amount'] ?? 0),
            currency: $rawData['currency'] ?? 'IDR',
            rawData: $rawData
        );
    }

    public function refundPayment(Payment $payment, float $amount, ?string $reason = null): bool
    {
        try {
            $payload = [
                'refund_key' => 'refund-' . $payment->transaction_id . '-' . time(),
                'amount' => (int) $amount,
                'reason' => $reason ?? 'Refund request',
            ];

            $response = Http::withBasicAuth($this->config['server_key'], '')
                ->post("{$this->baseUrl}/v2/{$payment->transaction_id}/refund", $payload);

            return $response->successful();
        } catch (\Exception $e) {
            Log::error('Midtrans refund exception', [
                'payment_id' => $payment->id,
                'message' => $e->getMessage(),
            ]);

            return false;
        }
    }

    public function getConfigSchema(): array
    {
        return [
            'server_key' => [
                'type' => 'text',
                'label' => 'Server Key',
                'required' => true,
                'encrypted' => true,
            ],
            'client_key' => [
                'type' => 'text',
                'label' => 'Client Key',
                'required' => true,
            ],
            'test_mode' => [
                'type' => 'boolean',
                'label' => 'Test Mode (Sandbox)',
                'default' => true,
            ],
        ];
    }

    public function validateConfig(array $config): bool
    {
        return !empty($config['server_key']) && !empty($config['client_key']);
    }

    public function healthCheck(): HealthCheckResult
    {
        try {
            // Test dengan get status dummy transaction
            $response = Http::withBasicAuth($this->config['server_key'], '')
                ->get("{$this->baseUrl}/v2/ping");

            if ($response->successful()) {
                return HealthCheckResult::ok('Midtrans API is reachable');
            }

            return HealthCheckResult::error('Failed to connect to Midtrans API', [
                'status_code' => $response->status(),
            ]);
        } catch (\Exception $e) {
            return HealthCheckResult::error('Midtrans connection error: ' . $e->getMessage());
        }
    }

    public function getCallbackUrl(): string
    {
        return route('payment.callback', ['gateway' => 'midtrans']);
    }
}
