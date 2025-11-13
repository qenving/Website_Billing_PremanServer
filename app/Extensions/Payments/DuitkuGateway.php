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

class DuitkuGateway implements PaymentGatewayInterface
{
    protected array $config;
    protected string $baseUrl;

    public function __construct(array $config)
    {
        $this->config = $config;
        $this->baseUrl = ($config['mode'] ?? 'sandbox') === 'live'
            ? 'https://passport.duitku.com/webapi/api'
            : 'https://sandbox.duitku.com/webapi/api';
    }

    public function createInvoice(Invoice $invoice, Client $client): array
    {
        try {
            $merchantCode = $this->config['merchant_code'];
            $apiKey = $this->config['api_key'];
            $merchantOrderId = $invoice->invoice_number;
            $paymentAmount = (int) $invoice->total;
            $paymentMethod = $this->config['default_payment_method'] ?? 'VC';
            $productDetails = 'Invoice ' . $invoice->invoice_number;
            $email = $client->user->email;
            $customerName = $client->user->name;
            $callbackUrl = $this->getCallbackUrl();
            $returnUrl = route('client.invoices.show', $invoice->id);
            $expiryPeriod = 1440; // 24 hours in minutes

            $params = [
                'merchantCode' => $merchantCode,
                'paymentAmount' => $paymentAmount,
                'paymentMethod' => $paymentMethod,
                'merchantOrderId' => $merchantOrderId,
                'productDetails' => $productDetails,
                'email' => $email,
                'customerVaName' => $customerName,
                'callbackUrl' => $callbackUrl,
                'returnUrl' => $returnUrl,
                'expiryPeriod' => $expiryPeriod,
            ];

            $signature = md5($merchantCode . $merchantOrderId . $paymentAmount . $apiKey);
            $params['signature'] = $signature;

            $response = Http::post($this->baseUrl . '/merchant/createinvoice', $params);

            if ($response->successful() && $response->json('statusCode') === '00') {
                $data = $response->json();
                return [
                    'payment_url' => $data['paymentUrl'],
                    'transaction_id' => $data['reference'],
                    'expires_at' => now()->addMinutes($expiryPeriod),
                ];
            }

            throw new \Exception('Failed to create Duitku invoice: ' . $response->json('statusMessage'));
        } catch (\Exception $e) {
            Log::error('Duitku createInvoice error', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    public function getPaymentStatus(string $referenceId): string
    {
        try {
            $merchantCode = $this->config['merchant_code'];
            $apiKey = $this->config['api_key'];
            $signature = md5($merchantCode . $referenceId . $apiKey);

            $response = Http::post($this->baseUrl . '/merchant/transactionStatus', [
                'merchantCode' => $merchantCode,
                'merchantOrderId' => $referenceId,
                'signature' => $signature,
            ]);

            if ($response->successful()) {
                $statusCode = $response->json('statusCode');
                return match($statusCode) {
                    '00' => 'completed',
                    '01' => 'pending',
                    '02' => 'failed',
                    default => 'pending',
                };
            }

            return 'pending';
        } catch (\Exception $e) {
            Log::error('Duitku getPaymentStatus error', ['error' => $e->getMessage()]);
            return 'pending';
        }
    }

    public function handleCallback(Request $request): PaymentCallbackResult
    {
        $merchantCode = $request->input('merchantCode');
        $amount = $request->input('amount');
        $merchantOrderId = $request->input('merchantOrderId');
        $signature = $request->input('signature');

        $apiKey = $this->config['api_key'];
        $calculatedSignature = md5($merchantCode . $amount . $merchantOrderId . $apiKey);

        $result = new PaymentCallbackResult();
        $result->transactionId = $merchantOrderId;
        $result->amount = $amount;
        $result->rawPayload = $request->all();

        if ($signature !== $calculatedSignature) {
            $result->success = false;
            $result->status = 'failed';
            $result->errorMessage = 'Invalid signature';
            return $result;
        }

        $resultCode = $request->input('resultCode');
        if ($resultCode === '00') {
            $result->success = true;
            $result->status = 'completed';
        } else {
            $result->success = false;
            $result->status = 'failed';
            $result->errorMessage = $request->input('resultMessage');
        }

        return $result;
    }

    public function refundPayment(Payment $payment, float $amount, ?string $reason = null): bool
    {
        // Duitku doesn't support automatic refunds via API
        // Manual refund through dashboard required
        Log::info('Duitku refund requested (manual process)', [
            'payment_id' => $payment->id,
            'amount' => $amount,
            'reason' => $reason,
        ]);
        return false;
    }

    public function getConfigSchema(): array
    {
        return [
            'merchant_code' => [
                'type' => 'text',
                'label' => 'Merchant Code',
                'required' => true,
                'description' => 'Your Duitku merchant code',
            ],
            'api_key' => [
                'type' => 'text',
                'label' => 'API Key',
                'required' => true,
                'description' => 'Your Duitku API key',
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
                    'VC' => 'Credit Card',
                    'VA' => 'Virtual Account',
                    'BC' => 'BCA KlikPay',
                    'M2' => 'Mandiri Clickpay',
                ],
                'default' => 'VC',
            ],
        ];
    }

    public function validateConfig(array $config): bool
    {
        return !empty($config['merchant_code']) && !empty($config['api_key']);
    }

    public function healthCheck(): HealthCheckResult
    {
        $result = new HealthCheckResult();
        $result->serviceName = 'Duitku Payment Gateway';

        try {
            $response = Http::timeout(10)->get($this->baseUrl . '/merchant/paymentmethod/getpaymentmethod', [
                'merchantcode' => $this->config['merchant_code'] ?? '',
                'amount' => 10000,
            ]);

            if ($response->successful()) {
                $result->status = 'ok';
                $result->message = 'Connection successful';
            } else {
                $result->status = 'error';
                $result->message = 'Failed to connect: ' . $response->status();
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
        return route('payment.callback', ['gateway' => 'duitku']);
    }
}
