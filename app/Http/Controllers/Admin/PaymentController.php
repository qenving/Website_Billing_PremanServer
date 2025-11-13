<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Client;
use App\Models\Invoice;
use App\Models\Payment;
use App\Services\ExtensionManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PaymentController extends Controller
{
    protected ExtensionManager $extensionManager;

    public function __construct(ExtensionManager $extensionManager)
    {
        $this->extensionManager = $extensionManager;
    }

    public function index(Request $request)
    {
        $query = Payment::with(['invoice.client.user', 'gateway']);

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by gateway
        if ($request->filled('gateway_id')) {
            $query->where('gateway_id', $request->gateway_id);
        }

        // Filter by client
        if ($request->filled('client_id')) {
            $query->whereHas('invoice', function($q) use ($request) {
                $q->where('client_id', $request->client_id);
            });
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Search by transaction ID
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('transaction_id', 'like', "%{$search}%")
                  ->orWhere('gateway_transaction_id', 'like', "%{$search}%")
                  ->orWhereHas('invoice', function($q2) use ($search) {
                      $q2->where('invoice_number', 'like', "%{$search}%");
                  });
            });
        }

        $payments = $query->latest()->paginate(20);
        $clients = Client::with('user')->get();
        $gateways = \App\Models\Extension::where('type', 'payment_gateway')->get();

        return view('admin.payments.index', compact('payments', 'clients', 'gateways'));
    }

    public function show(Payment $payment)
    {
        $payment->load([
            'invoice.client.user',
            'invoice.service.product',
            'gateway',
            'refunds'
        ]);

        return view('admin.payments.show', compact('payment'));
    }

    public function refund(Request $request, Payment $payment)
    {
        // Validate refund can be processed
        if ($payment->status !== 'completed') {
            return back()->with('error', 'Only completed payments can be refunded.');
        }

        // Check if already refunded
        if ($payment->refunds()->where('status', 'completed')->exists()) {
            return back()->with('error', 'This payment has already been refunded.');
        }

        $request->validate([
            'amount' => 'required|numeric|min:0.01|max:' . $payment->amount,
            'reason' => 'required|string|max:500',
        ]);

        DB::beginTransaction();

        try {
            // Get payment gateway
            $gateway = $this->extensionManager->getPaymentGateway($payment->gateway->name);

            if (!$gateway) {
                throw new \Exception('Payment gateway not found or not configured.');
            }

            // Process refund through gateway
            $result = $gateway->refundPayment($payment->gateway_transaction_id, $request->amount);

            if (!$result['success']) {
                throw new \Exception($result['message'] ?? 'Refund failed');
            }

            // Create refund record
            $refund = $payment->refunds()->create([
                'amount' => $request->amount,
                'reason' => $request->reason,
                'status' => 'completed',
                'refund_transaction_id' => $result['refund_id'] ?? null,
                'refunded_by' => auth()->id(),
                'refunded_at' => now(),
            ]);

            // Update payment status if fully refunded
            $totalRefunded = $payment->refunds()->where('status', 'completed')->sum('amount');
            if ($totalRefunded >= $payment->amount) {
                $payment->update(['status' => 'refunded']);

                // Update invoice status back to unpaid
                $payment->invoice->update(['status' => 'unpaid']);
            }

            DB::commit();

            // Audit log
            AuditLog::create([
                'user_id' => auth()->id(),
                'action' => 'payment.refunded',
                'description' => "Refunded {$request->amount} for payment {$payment->transaction_id}",
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            return back()->with('success', 'Payment refunded successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Refund failed: ' . $e->getMessage());
        }
    }

    public function markAsCompleted(Payment $payment)
    {
        if ($payment->status === 'completed') {
            return back()->with('error', 'Payment is already marked as completed.');
        }

        DB::beginTransaction();

        try {
            $payment->update([
                'status' => 'completed',
                'paid_at' => now(),
            ]);

            // Update invoice
            $invoice = $payment->invoice;
            $totalPaid = $invoice->payments()->where('status', 'completed')->sum('amount');

            if ($totalPaid >= $invoice->total) {
                $invoice->update([
                    'status' => 'paid',
                    'paid_at' => now(),
                ]);

                // If invoice has associated service, activate it
                if ($invoice->service) {
                    if ($invoice->service->status === 'pending') {
                        $invoice->service->update(['status' => 'active']);
                    }
                }
            }

            DB::commit();

            // Audit log
            AuditLog::create([
                'user_id' => auth()->id(),
                'action' => 'payment.marked_completed',
                'description' => "Manually marked payment as completed: {$payment->transaction_id}",
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);

            return back()->with('success', 'Payment marked as completed successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to mark payment as completed: ' . $e->getMessage());
        }
    }

    public function cancel(Payment $payment)
    {
        if ($payment->status === 'completed') {
            return back()->with('error', 'Cannot cancel a completed payment. Use refund instead.');
        }

        if ($payment->status === 'cancelled') {
            return back()->with('error', 'Payment is already cancelled.');
        }

        $payment->update(['status' => 'cancelled']);

        // Audit log
        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'payment.cancelled',
            'description' => "Cancelled payment: {$payment->transaction_id}",
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        return back()->with('success', 'Payment cancelled successfully.');
    }

    public function checkStatus(Payment $payment)
    {
        try {
            $gateway = $this->extensionManager->getPaymentGateway($payment->gateway->name);

            if (!$gateway) {
                throw new \Exception('Payment gateway not found or not configured.');
            }

            $status = $gateway->getPaymentStatus($payment->gateway_transaction_id);

            // Update payment status if different
            if ($status['status'] !== $payment->status) {
                $payment->update([
                    'status' => $status['status'],
                    'gateway_response' => json_encode($status),
                ]);

                if ($status['status'] === 'completed') {
                    // Update invoice
                    $invoice = $payment->invoice;
                    $totalPaid = $invoice->payments()->where('status', 'completed')->sum('amount');

                    if ($totalPaid >= $invoice->total) {
                        $invoice->update([
                            'status' => 'paid',
                            'paid_at' => now(),
                        ]);
                    }
                }
            }

            // Audit log
            AuditLog::create([
                'user_id' => auth()->id(),
                'action' => 'payment.status_checked',
                'description' => "Checked status for payment: {$payment->transaction_id} - Status: {$status['status']}",
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);

            return back()->with('success', "Payment status updated: {$status['status']}");

        } catch (\Exception $e) {
            return back()->with('error', 'Failed to check payment status: ' . $e->getMessage());
        }
    }

    public function export(Request $request)
    {
        $request->validate([
            'date_from' => 'required|date',
            'date_to' => 'required|date|after_or_equal:date_from',
            'status' => 'nullable|in:pending,completed,failed,refunded,cancelled',
        ]);

        $query = Payment::with(['invoice.client.user', 'gateway'])
            ->whereBetween('created_at', [$request->date_from, $request->date_to]);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $payments = $query->get();

        $filename = 'payments_' . date('Y-m-d_His') . '.csv';

        $callback = function() use ($payments) {
            $file = fopen('php://output', 'w');

            // Headers
            fputcsv($file, [
                'Transaction ID',
                'Gateway Transaction ID',
                'Invoice Number',
                'Client Name',
                'Client Email',
                'Gateway',
                'Amount',
                'Status',
                'Date',
                'Paid At',
            ]);

            // Data
            foreach ($payments as $payment) {
                fputcsv($file, [
                    $payment->transaction_id,
                    $payment->gateway_transaction_id ?? 'N/A',
                    $payment->invoice->invoice_number,
                    $payment->invoice->client->user->name,
                    $payment->invoice->client->user->email,
                    $payment->gateway->display_name,
                    number_format($payment->amount, 2),
                    ucfirst($payment->status),
                    $payment->created_at->format('Y-m-d H:i:s'),
                    $payment->paid_at?->format('Y-m-d H:i:s') ?? 'N/A',
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ]);
    }
}
