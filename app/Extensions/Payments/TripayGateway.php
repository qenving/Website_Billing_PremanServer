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

class TripayGateway implements PaymentGatewayInterface
{
    protected array $config;
    protected string $baseUrl;

    public function __construct(array $config)
    {
        $this->config = $config;
        $this->baseUrl = ($config['mode'] ?? 'sandbox') === 'live'
            ? 'https://tripay.co.id/api'
            : 'https://tripay.co.id/api-sandbox';
    }

    public function createInvoice(Invoice $invoice, Client $client): array
    {
        try {
            $apiKey = $this->config['api_key'];
            $privateKey = $this->config['private_key'];
            $merchantCode = $this->config['merchant_code'];
            $merchantRef = $invoice->invoice_number;
            $amount = (int) $invoice->total;
            $method = $this->config['default_payment_method'] ?? 'BRIVA';

            $data = [
                'method' => $method,
                'merchant_ref' => $merchantRef,
                'amount' => $amount,
                'customer_name' => $client->user->name,
                'customer_email' => $client->user->email,
                'customer_phone' => $client->company_phone ?? '081234567890',
                'order_items' => [
                    [
                        'name' => 'Invoice ' . $invoice->invoice_number,
                        'price' => $amount,
                        'quantity' => 1,
                    ]
                ],
                'callback_url' => $this->getCallbackUrl(),
                'return_url' => route('client.invoices.show', $invoice->id),
                'expired_time' => (time() + (24 * 60 * 60)), // 24 hours
            ];

            $signature = hash_hmac('sha256', $merchantCode . $merchantRef . $amount, $privateKey);

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $apiKey,
            ])->post($this->baseUrl . '/transaction/create', array_merge($data, [
                'signature' => $signature,
            ]));

            if ($response->successful() && $response->json('success')) {
                $transaction = $response->json('data');
                return [
                    'payment_url' => $transaction['checkout_url'],
                    'transaction_id' => $transaction['reference'],
                    'expires_at' => now()->addSeconds($transaction['expired_time'] - time()),
                ];
            }

            throw new \Exception('Failed to create Tripay transaction: ' . $response->json('message'));
        } catch (\Exception $e) {
            Log::error('Tripay createInvoice error', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    public function getPaymentStatus(string $referenceId): string
    {
        try {
            $apiKey = $this->config['api_key'];

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $apiKey,
            ])->get($this->baseUrl . '/transaction/detail', [
                'reference' => $referenceId,
            ]);

            if ($response->successful()) {
                $status = $response->json('data.status');
                return match($status) {
                    'PAID' => 'completed',
                    'UNPAID' => 'pending',
                    'EXPIRED', 'FAILED', 'REFUND' => 'failed',
                    default => 'pending',
                };
            }

            return 'pending';
        } catch (\Exception $e) {
            Log::error('Tripay getPaymentStatus error', ['error' => $e->getMessage()]);
            return 'pending';
        }
    }

    public function handleCallback(Request $request): PaymentCallbackResult
    {
        $callbackSignature = $request->server('HTTP_X_CALLBACK_SIGNATURE');
        $privateKey = $this->config['private_key'];
        $json = $request->getContent();
        $signature = hash_hmac('sha256', $json, $privateKey);

        $result = new PaymentCallbackResult();
        $result->transactionId = $request->input('merchant_ref');
        $result->amount = $request->input('amount');
        $result->rawPayload = $request->all();

        if ($callbackSignature !== $signature) {
            $result->success = false;
            $result->status = 'failed';
            $result->errorMessage = 'Invalid signature';
            return $result;
        }

        $status = $request->input('status');
        if ($status === 'PAID') {
            $result->success = true;
            $result->status = 'completed';
        } else {
            $result->success = false;
            $result->status = 'failed';
            $result->errorMessage = 'Payment not completed';
        }

        return $result;
    }

    public function refundPayment(Payment $payment, float $amount, ?string $reason = null): bool
    {
        // Tripay supports refund but requires manual approval
        Log::info('Tripay refund requested', [
            'payment_id' => $payment->id,
            'amount' => $amount,
            'reason' => $reason,
        ]);
        return false;
    }

    public function getConfigSchema(): array
    {
        return [
            'api_key' => [
                'type' => 'text',
                'label' => 'API Key',
                'required' => true,
                'description' => 'Your Tripay API key',
            ],
            'private_key' => [
                'type' => 'text',
                'label' => 'Private Key',
                'required' => true,
                'description' => 'Your Tripay private key',
            ],
            'merchant_code' => [
                'type' => 'text',
                'label' => 'Merchant Code',
                'required' => true,
                'description' => 'Your Tripay merchant code',
            ],
            'mode' => [
                'type' => 'select',
                'label' => 'Mode',
                'required' => true,
                'options' => ['sandbox' => 'Sandbox', 'live' => 'Live'],
                'default' => 'sandbox',
            ],
            'default_payment_method' => [
                'type' => 'select',
                'label' => 'Default Payment Method',
                'required' => false,
                'options' => [
                    'BRIVA' => 'BRI Virtual Account',
                    'BCAVA' => 'BCA Virtual Account',
                    'MANDIRIVA' => 'Mandiri Virtual Account',
                    'QRIS' => 'QRIS',
                ],
                'default' => 'BRIVA',
            ],
        ];
    }

    public function validateConfig(array $config): bool
    {
        return !empty($config['api_key']) && !empty($config['private_key']) && !empty($config['merchant_code']);
    }

    public function healthCheck(): HealthCheckResult
    {
        $result = new HealthCheckResult();
        $result->serviceName = 'Tripay Payment Gateway';

        try {
            $response = Http::timeout(10)
                ->withHeaders(['Authorization' => 'Bearer ' . ($this->config['api_key'] ?? '')])
                ->get($this->baseUrl . '/merchant/payment-channel');

            if ($response->successful() && $response->json('success')) {
                $result->status = 'ok';
                $result->message = 'Connection successful';
            } else {
                $result->status = 'error';
                $result->message = 'Failed to connect: ' . $response->json('message');
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
        return route('payment.callback', ['gateway' => 'tripay']);
    }
}
