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

class PaypalGateway implements PaymentGatewayInterface
{
    protected array $config;
    protected string $baseUrl;

    public function __construct(array $config)
    {
        $this->config = $config;
        $this->baseUrl = ($config['mode'] ?? 'sandbox') === 'live'
            ? 'https://api-m.paypal.com'
            : 'https://api-m.sandbox.paypal.com';
    }

    protected function getAccessToken(): string
    {
        $response = Http::withBasicAuth(
            $this->config['client_id'],
            $this->config['client_secret']
        )->asForm()->post($this->baseUrl . '/v1/oauth2/token', [
            'grant_type' => 'client_credentials',
        ]);

        if ($response->successful()) {
            return $response->json('access_token');
        }

        throw new \Exception('Failed to get PayPal access token');
    }

    public function createInvoice(Invoice $invoice, Client $client): array
    {
        try {
            $accessToken = $this->getAccessToken();

            $data = [
                'intent' => 'CAPTURE',
                'purchase_units' => [
                    [
                        'reference_id' => $invoice->invoice_number,
                        'amount' => [
                            'currency_code' => $invoice->currency,
                            'value' => number_format($invoice->total, 2, '.', ''),
                        ],
                        'description' => 'Invoice ' . $invoice->invoice_number,
                    ]
                ],
                'application_context' => [
                    'return_url' => route('client.invoices.show', $invoice->id) . '?paypal_success=1',
                    'cancel_url' => route('client.invoices.show', $invoice->id) . '?paypal_cancel=1',
                    'brand_name' => config('app.name'),
                    'shipping_preference' => 'NO_SHIPPING',
                ],
            ];

            $response = Http::withToken($accessToken)
                ->post($this->baseUrl . '/v2/checkout/orders', $data);

            if ($response->successful()) {
                $order = $response->json();
                $approveLink = collect($order['links'])->firstWhere('rel', 'approve');

                return [
                    'payment_url' => $approveLink['href'],
                    'transaction_id' => $order['id'],
                    'expires_at' => now()->addHours(3),
                ];
            }

            throw new \Exception('Failed to create PayPal order: ' . $response->body());
        } catch (\Exception $e) {
            Log::error('PayPal createInvoice error', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    public function getPaymentStatus(string $referenceId): string
    {
        try {
            $accessToken = $this->getAccessToken();

            $response = Http::withToken($accessToken)
                ->get($this->baseUrl . '/v2/checkout/orders/' . $referenceId);

            if ($response->successful()) {
                $status = $response->json('status');
                return match($status) {
                    'COMPLETED', 'APPROVED' => 'completed',
                    'CREATED', 'SAVED' => 'pending',
                    'VOIDED', 'EXPIRED' => 'failed',
                    default => 'pending',
                };
            }

            return 'pending';
        } catch (\Exception $e) {
            Log::error('PayPal getPaymentStatus error', ['error' => $e->getMessage()]);
            return 'pending';
        }
    }

    public function handleCallback(Request $request): PaymentCallbackResult
    {
        // PayPal uses return URL method, not webhook for basic implementation
        $result = new PaymentCallbackResult();
        $result->transactionId = $request->input('token'); // PayPal order ID
        $result->rawPayload = $request->all();

        try {
            $status = $this->getPaymentStatus($result->transactionId);

            if ($status === 'completed') {
                // Capture the order
                $accessToken = $this->getAccessToken();
                $captureResponse = Http::withToken($accessToken)
                    ->post($this->baseUrl . '/v2/checkout/orders/' . $result->transactionId . '/capture');

                if ($captureResponse->successful()) {
                    $capture = $captureResponse->json();
                    $result->success = true;
                    $result->status = 'completed';
                    $result->amount = $capture['purchase_units'][0]['payments']['captures'][0]['amount']['value'];
                } else {
                    $result->success = false;
                    $result->status = 'failed';
                    $result->errorMessage = 'Failed to capture payment';
                }
            } else {
                $result->success = false;
                $result->status = 'pending';
            }
        } catch (\Exception $e) {
            $result->success = false;
            $result->status = 'failed';
            $result->errorMessage = $e->getMessage();
        }

        return $result;
    }

    public function refundPayment(Payment $payment, float $amount, ?string $reason = null): bool
    {
        try {
            $accessToken = $this->getAccessToken();
            $captureId = $payment->transaction_id;

            $response = Http::withToken($accessToken)
                ->post($this->baseUrl . '/v2/payments/captures/' . $captureId . '/refund', [
                    'amount' => [
                        'value' => number_format($amount, 2, '.', ''),
                        'currency_code' => $payment->currency ?? 'USD',
                    ],
                    'note_to_payer' => $reason ?? 'Refund processed',
                ]);

            return $response->successful();
        } catch (\Exception $e) {
            Log::error('PayPal refund error', ['error' => $e->getMessage()]);
            return false;
        }
    }

    public function getConfigSchema(): array
    {
        return [
            'client_id' => [
                'type' => 'text',
                'label' => 'Client ID',
                'required' => true,
                'description' => 'Your PayPal REST API Client ID',
            ],
            'client_secret' => [
                'type' => 'password',
                'label' => 'Client Secret',
                'required' => true,
                'description' => 'Your PayPal REST API Client Secret',
            ],
            'mode' => [
                'type' => 'select',
                'label' => 'Mode',
                'required' => true,
                'options' => ['sandbox' => 'Sandbox', 'live' => 'Live'],
                'default' => 'sandbox',
            ],
        ];
    }

    public function validateConfig(array $config): bool
    {
        return !empty($config['client_id']) && !empty($config['client_secret']);
    }

    public function healthCheck(): HealthCheckResult
    {
        $result = new HealthCheckResult();
        $result->serviceName = 'PayPal Payment Gateway';

        try {
            $accessToken = $this->getAccessToken();

            $result->status = 'ok';
            $result->message = 'Connection successful';
        } catch (\Exception $e) {
            $result->status = 'error';
            $result->message = 'Connection error: ' . $e->getMessage();
        }

        $result->checkedAt = now();
        return $result;
    }

    public function getCallbackUrl(): string
    {
        return route('payment.callback', ['gateway' => 'paypal']);
    }
}
