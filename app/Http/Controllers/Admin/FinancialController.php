<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\PaymentTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FinancialController extends Controller
{
    public function index(Request $request)
    {
        $period = $request->get('period', 'this_month');

        // Date ranges
        $dates = $this->getDateRange($period);

        // Revenue stats
        $revenue = [
            'total' => Payment::where('status', 'completed')
                ->whereBetween('created_at', [$dates['start'], $dates['end']])
                ->sum('amount'),
            'count' => Payment::where('status', 'completed')
                ->whereBetween('created_at', [$dates['start'], $dates['end']])
                ->count(),
        ];

        // Revenue by gateway
        $revenueByGateway = Payment::where('status', 'completed')
            ->whereBetween('created_at', [$dates['start'], $dates['end']])
            ->select('gateway', DB::raw('SUM(amount) as total'), DB::raw('COUNT(*) as count'))
            ->groupBy('gateway')
            ->get();

        // Revenue by product
        $revenueByProduct = DB::table('payments')
            ->join('invoices', 'payments.invoice_id', '=', 'invoices.id')
            ->join('invoice_items', 'invoices.id', '=', 'invoice_items.invoice_id')
            ->join('products', 'invoice_items.product_id', '=', 'products.id')
            ->where('payments.status', 'completed')
            ->whereBetween('payments.created_at', [$dates['start'], $dates['end']])
            ->select('products.name', DB::raw('SUM(invoice_items.amount) as total'))
            ->groupBy('products.id', 'products.name')
            ->orderByDesc('total')
            ->limit(10)
            ->get();

        // Invoice stats
        $invoiceStats = [
            'paid' => Invoice::where('status', 'paid')
                ->whereBetween('created_at', [$dates['start'], $dates['end']])
                ->count(),
            'unpaid' => Invoice::where('status', 'unpaid')
                ->whereBetween('created_at', [$dates['start'], $dates['end']])
                ->count(),
            'overdue' => Invoice::where('status', 'overdue')
                ->whereBetween('created_at', [$dates['start'], $dates['end']])
                ->count(),
            'cancelled' => Invoice::where('status', 'cancelled')
                ->whereBetween('created_at', [$dates['start'], $dates['end']])
                ->count(),
        ];

        // Daily revenue chart
        $dailyRevenue = Payment::where('status', 'completed')
            ->whereBetween('created_at', [$dates['start'], $dates['end']])
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('SUM(amount) as total')
            )
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Recent transactions
        $recentTransactions = PaymentTransaction::with('payment.invoice.client.user')
            ->latest()
            ->limit(20)
            ->get();

        return view('admin.financial.index', compact(
            'revenue',
            'revenueByGateway',
            'revenueByProduct',
            'invoiceStats',
            'dailyRevenue',
            'recentTransactions',
            'period'
        ));
    }

    public function export(Request $request)
    {
        $request->validate([
            'period' => 'required|in:today,this_week,this_month,this_year,custom',
            'start_date' => 'required_if:period,custom|date',
            'end_date' => 'required_if:period,custom|date|after_or_equal:start_date',
        ]);

        $dates = $this->getDateRange(
            $request->period,
            $request->start_date,
            $request->end_date
        );

        $payments = Payment::with(['invoice.client.user', 'invoice.items'])
            ->where('status', 'completed')
            ->whereBetween('created_at', [$dates['start'], $dates['end']])
            ->get();

        // Generate CSV
        $filename = 'financial_report_' . now()->format('Y-m-d_His') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function() use ($payments) {
            $file = fopen('php://output', 'w');

            // Headers
            fputcsv($file, [
                'Date',
                'Invoice Number',
                'Client',
                'Amount',
                'Currency',
                'Gateway',
                'Transaction ID',
                'Status',
            ]);

            // Data
            foreach ($payments as $payment) {
                fputcsv($file, [
                    $payment->created_at->format('Y-m-d H:i:s'),
                    $payment->invoice->invoice_number,
                    $payment->invoice->client->user->name,
                    $payment->amount,
                    $payment->currency,
                    $payment->gateway,
                    $payment->transaction_id,
                    $payment->status,
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    protected function getDateRange($period, $startDate = null, $endDate = null)
    {
        $now = now();

        return match($period) {
            'today' => [
                'start' => $now->copy()->startOfDay(),
                'end' => $now->copy()->endOfDay(),
            ],
            'this_week' => [
                'start' => $now->copy()->startOfWeek(),
                'end' => $now->copy()->endOfWeek(),
            ],
            'this_month' => [
                'start' => $now->copy()->startOfMonth(),
                'end' => $now->copy()->endOfMonth(),
            ],
            'this_year' => [
                'start' => $now->copy()->startOfYear(),
                'end' => $now->copy()->endOfYear(),
            ],
            'custom' => [
                'start' => \Carbon\Carbon::parse($startDate)->startOfDay(),
                'end' => \Carbon\Carbon::parse($endDate)->endOfDay(),
            ],
            default => [
                'start' => $now->copy()->startOfMonth(),
                'end' => $now->copy()->endOfMonth(),
            ],
        };
    }
}
