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

class CryptomusGateway implements PaymentGatewayInterface
{
    protected array $config;
    protected string $baseUrl = 'https://api.cryptomus.com/v1';

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    protected function generateSignature(array $data): string
    {
        $json = json_encode($data, JSON_UNESCAPED_UNICODE);
        $encoded = base64_encode($json);
        return hash('md5', $encoded . $this->config['api_key']);
    }

    public function createInvoice(Invoice $invoice, Client $client): array
    {
        try {
            $merchantId = $this->config['merchant_id'];
            $currency = $this->config['default_currency'] ?? 'USDT';

            $data = [
                'amount' => (string) $invoice->total,
                'currency' => $invoice->currency,
                'to_currency' => $currency,
                'order_id' => $invoice->invoice_number,
                'url_callback' => $this->getCallbackUrl(),
                'url_return' => route('client.invoices.show', $invoice->id),
                'url_success' => route('client.invoices.show', $invoice->id) . '?crypto_success=1',
                'is_payment_multiple' => false,
                'lifetime' => 3600, // 1 hour
            ];

            $signature = $this->generateSignature($data);

            $response = Http::withHeaders([
                'merchant' => $merchantId,
                'sign' => $signature,
                'Content-Type' => 'application/json',
            ])->post($this->baseUrl . '/payment', $data);

            if ($response->successful() && $response->json('state') === 0) {
                $result = $response->json('result');

                // Get crypto conversion rate for logging
                $rate = $result['amount'] ?? 0;

                return [
                    'payment_url' => $result['url'],
                    'transaction_id' => $result['uuid'],
                    'expires_at' => now()->addSeconds($data['lifetime']),
                    'crypto_amount' => $result['amount'] ?? null,
                    'crypto_currency' => $currency,
                    'conversion_rate' => $rate ? ($invoice->total / $rate) : null,
                ];
            }

            throw new \Exception('Failed to create Cryptomus invoice: ' . $response->json('message'));
        } catch (\Exception $e) {
            Log::error('Cryptomus createInvoice error', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    public function getPaymentStatus(string $referenceId): string
    {
        try {
            $merchantId = $this->config['merchant_id'];
            $data = ['uuid' => $referenceId];
            $signature = $this->generateSignature($data);

            $response = Http::withHeaders([
                'merchant' => $merchantId,
                'sign' => $signature,
                'Content-Type' => 'application/json',
            ])->post($this->baseUrl . '/payment/info', $data);

            if ($response->successful() && $response->json('state') === 0) {
                $status = $response->json('result.payment_status');
                return match($status) {
                    'paid', 'paid_over' => 'completed',
                    'process', 'check', 'confirm_check' => 'pending',
                    'wrong_amount', 'cancel', 'fail', 'wrong_amount_waiting' => 'failed',
                    'expired' => 'expired',
                    default => 'pending',
                };
            }

            return 'pending';
        } catch (\Exception $e) {
            Log::error('Cryptomus getPaymentStatus error', ['error' => $e->getMessage()]);
            return 'pending';
        }
    }

    public function handleCallback(Request $request): PaymentCallbackResult
    {
        $result = new PaymentCallbackResult();

        try {
            $data = $request->all();
            $receivedSign = $request->header('Sign');

            // Verify signature
            $sign = hash('md5', base64_encode(json_encode($data, JSON_UNESCAPED_UNICODE)) . $this->config['api_key']);

            if ($receivedSign !== $sign) {
                $result->success = false;
                $result->status = 'failed';
                $result->errorMessage = 'Invalid signature';
                return $result;
            }

            $result->transactionId = $data['order_id'];
            $result->amount = $data['amount'] ?? 0;
            $result->rawPayload = $data;

            $status = $data['status'];
            if (in_array($status, ['paid', 'paid_over'])) {
                $result->success = true;
                $result->status = 'completed';
                $result->cryptoAmount = $data['payer_amount'] ?? null;
                $result->cryptoCurrency = $data['payer_currency'] ?? null;
            } else {
                $result->success = false;
                $result->status = 'pending';
            }
        } catch (\Exception $e) {
            Log::error('Cryptomus handleCallback error', ['error' => $e->getMessage()]);
            $result->success = false;
            $result->status = 'failed';
            $result->errorMessage = $e->getMessage();
        }

        return $result;
    }

    public function refundPayment(Payment $payment, float $amount, ?string $reason = null): bool
    {
        try {
            $merchantId = $this->config['merchant_id'];
            $data = [
                'uuid' => $payment->transaction_id,
                'address' => $payment->refund_address ?? '',
            ];
            $signature = $this->generateSignature($data);

            $response = Http::withHeaders([
                'merchant' => $merchantId,
                'sign' => $signature,
                'Content-Type' => 'application/json',
            ])->post($this->baseUrl . '/payment/refund', $data);

            return $response->successful() && $response->json('state') === 0;
        } catch (\Exception $e) {
            Log::error('Cryptomus refund error', ['error' => $e->getMessage()]);
            return false;
        }
    }

    public function getConfigSchema(): array
    {
        return [
            'merchant_id' => [
                'type' => 'text',
                'label' => 'Merchant ID',
                'required' => true,
                'description' => 'Your Cryptomus merchant ID',
            ],
            'api_key' => [
                'type' => 'password',
                'label' => 'API Key',
                'required' => true,
                'description' => 'Your Cryptomus API key',
            ],
            'default_currency' => [
                'type' => 'select',
                'label' => 'Default Crypto Currency',
                'required' => false,
                'options' => [
                    'USDT' => 'USDT (Tether)',
                    'BTC' => 'Bitcoin',
                    'ETH' => 'Ethereum',
                    'LTC' => 'Litecoin',
                    'TRX' => 'Tron',
                ],
                'default' => 'USDT',
            ],
        ];
    }

    public function validateConfig(array $config): bool
    {
        return !empty($config['merchant_id']) && !empty($config['api_key']);
    }

    public function healthCheck(): HealthCheckResult
    {
        $result = new HealthCheckResult();
        $result->serviceName = 'Cryptomus Payment Gateway';

        try {
            $merchantId = $this->config['merchant_id'];
            $data = ['limit' => 1];
            $signature = $this->generateSignature($data);

            $response = Http::timeout(10)->withHeaders([
                'merchant' => $merchantId,
                'sign' => $signature,
                'Content-Type' => 'application/json',
            ])->post($this->baseUrl . '/payment/list', $data);

            if ($response->successful() && $response->json('state') === 0) {
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
        return route('payment.callback', ['gateway' => 'cryptomus']);
    }
}
