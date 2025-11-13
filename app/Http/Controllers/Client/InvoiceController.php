<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Payment;
use App\Services\ExtensionManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class InvoiceController extends Controller
{
    protected ExtensionManager $extensionManager;

    public function __construct(ExtensionManager $extensionManager)
    {
        $this->extensionManager = $extensionManager;
    }

    public function index(Request $request)
    {
        $client = Auth::user()->client;

        $query = Invoice::where('client_id', $client->id);

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $invoices = $query->with('service.product')->latest()->paginate(20);

        // Statistics
        $stats = [
            'total_invoices' => Invoice::where('client_id', $client->id)->count(),
            'unpaid' => Invoice::where('client_id', $client->id)->where('status', 'unpaid')->count(),
            'paid' => Invoice::where('client_id', $client->id)->where('status', 'paid')->count(),
            'total_amount' => Invoice::where('client_id', $client->id)->sum('total'),
            'unpaid_amount' => Invoice::where('client_id', $client->id)->where('status', 'unpaid')->sum('total'),
        ];

        return view('client.invoices.index', compact('invoices', 'stats'));
    }

    public function show(Invoice $invoice)
    {
        // Authorization check
        if ($invoice->client_id !== Auth::user()->client->id) {
            abort(403, 'Unauthorized access to this invoice.');
        }

        $invoice->load(['service.product', 'items', 'payments']);

        return view('client.invoices.show', compact('invoice'));
    }

    public function pay(Invoice $invoice)
    {
        // Authorization check
        if ($invoice->client_id !== Auth::user()->client->id) {
            abort(403, 'Unauthorized access to this invoice.');
        }

        // Check if already paid
        if ($invoice->status === 'paid') {
            return redirect()->route('client.invoices.show', $invoice)
                ->with('info', 'This invoice has already been paid.');
        }

        // Get available payment gateways
        $paymentGateways = \App\Models\Extension::where('type', 'payment_gateway')
            ->where('enabled', true)
            ->get();

        $client = Auth::user()->client;

        return view('client.invoices.pay', compact('invoice', 'paymentGateways', 'client'));
    }

    public function processPayment(Request $request, Invoice $invoice)
    {
        // Authorization check
        if ($invoice->client_id !== Auth::user()->client->id) {
            abort(403, 'Unauthorized access to this invoice.');
        }

        // Check if already paid
        if ($invoice->status === 'paid') {
            return redirect()->route('client.invoices.show', $invoice)
                ->with('info', 'This invoice has already been paid.');
        }

        $request->validate([
            'gateway_id' => 'required|exists:extensions,id',
            'use_credit' => 'boolean',
        ]);

        $client = Auth::user()->client;
        $gateway = \App\Models\Extension::findOrFail($request->gateway_id);

        DB::beginTransaction();

        try {
            $amountToPay = $invoice->total;

            // Apply credit if requested
            $creditUsed = 0;
            if ($request->boolean('use_credit') && $client->credit_balance > 0) {
                $creditUsed = min($client->credit_balance, $amountToPay);
                $amountToPay -= $creditUsed;

                // Deduct credit
                $client->decrement('credit_balance', $creditUsed);

                // Record credit transaction
                DB::table('credit_transactions')->insert([
                    'client_id' => $client->id,
                    'amount' => $creditUsed,
                    'type' => 'debit',
                    'description' => "Applied to invoice {$invoice->invoice_number}",
                    'balance_before' => $client->credit_balance + $creditUsed,
                    'balance_after' => $client->credit_balance,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            // If fully paid with credit
            if ($amountToPay <= 0) {
                $invoice->update([
                    'status' => 'paid',
                    'paid_at' => now(),
                ]);

                // Activate service if pending
                if ($invoice->service && $invoice->service->status === 'pending') {
                    $invoice->service->update(['status' => 'active']);
                }

                DB::commit();

                return redirect()->route('client.invoices.show', $invoice)
                    ->with('success', 'Invoice paid successfully using account credit!');
            }

            // Create payment record
            $transactionId = 'TXN-' . date('YmdHis') . '-' . strtoupper(substr(md5(uniqid()), 0, 8));

            $payment = Payment::create([
                'invoice_id' => $invoice->id,
                'gateway_id' => $gateway->id,
                'transaction_id' => $transactionId,
                'amount' => $amountToPay,
                'status' => 'pending',
            ]);

            DB::commit();

            // Get payment gateway handler
            $gatewayHandler = $this->extensionManager->getPaymentGateway($gateway->name);

            if (!$gatewayHandler) {
                throw new \Exception('Payment gateway not found or not configured.');
            }

            // Create payment invoice with gateway
            $result = $gatewayHandler->createInvoice($invoice, $client);

            if (!$result['success']) {
                throw new \Exception($result['message'] ?? 'Failed to create payment.');
            }

            // Update payment with gateway transaction ID
            $payment->update([
                'gateway_transaction_id' => $result['transaction_id'] ?? null,
                'gateway_response' => json_encode($result),
            ]);

            // Redirect to payment URL
            if (isset($result['payment_url'])) {
                return redirect($result['payment_url']);
            }

            return redirect()->route('client.invoices.show', $invoice)
                ->with('success', 'Payment initiated successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Payment failed: ' . $e->getMessage());
        }
    }

    public function downloadPdf(Invoice $invoice)
    {
        // Authorization check
        if ($invoice->client_id !== Auth::user()->client->id) {
            abort(403, 'Unauthorized access to this invoice.');
        }

        $invoice->load(['client.user', 'service.product', 'items']);

        $pdf = Pdf::loadView('client.invoices.pdf', compact('invoice'));

        return $pdf->download("{$invoice->invoice_number}.pdf");
    }

    public function paymentSuccess(Request $request, Invoice $invoice)
    {
        // Authorization check
        if ($invoice->client_id !== Auth::user()->client->id) {
            abort(403, 'Unauthorized access to this invoice.');
        }

        return view('client.invoices.payment-success', compact('invoice'));
    }

    public function paymentFailed(Request $request, Invoice $invoice)
    {
        // Authorization check
        if ($invoice->client_id !== Auth::user()->client->id) {
            abort(403, 'Unauthorized access to this invoice.');
        }

        $reason = $request->get('reason', 'Payment was not completed.');

        return view('client.invoices.payment-failed', compact('invoice', 'reason'));
    }
}
