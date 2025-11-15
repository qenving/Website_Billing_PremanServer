<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Service;
use App\Models\Client;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    /**
     * Display reports dashboard
     */
    public function index()
    {
        return view('admin.reports.index');
    }

    /**
     * Revenue report
     */
    public function revenue(Request $request)
    {
        $startDate = $request->input('start_date', Carbon::now()->subDays(30));
        $endDate = $request->input('end_date', Carbon::now());

        // Daily revenue
        $dailyRevenue = Payment::where('status', 'success')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->selectRaw('DATE(created_at) as date, SUM(amount) as total')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Revenue by gateway
        $revenueByGateway = Payment::where('status', 'success')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->selectRaw('gateway, SUM(amount) as total, COUNT(*) as count')
            ->groupBy('gateway')
            ->get();

        // Summary
        $summary = [
            'total_revenue' => Payment::where('status', 'success')
                ->whereBetween('created_at', [$startDate, $endDate])
                ->sum('amount'),
            'total_payments' => Payment::where('status', 'success')
                ->whereBetween('created_at', [$startDate, $endDate])
                ->count(),
            'avg_payment' => Payment::where('status', 'success')
                ->whereBetween('created_at', [$startDate, $endDate])
                ->avg('amount'),
            'pending_amount' => Invoice::where('status', 'unpaid')
                ->sum('total'),
        ];

        return view('admin.reports.revenue', compact('dailyRevenue', 'revenueByGateway', 'summary', 'startDate', 'endDate'));
    }

    /**
     * Sales report
     */
    public function sales(Request $request)
    {
        $startDate = $request->input('start_date', Carbon::now()->subDays(30));
        $endDate = $request->input('end_date', Carbon::now());

        // Sales by product
        $salesByProduct = Service::whereBetween('created_at', [$startDate, $endDate])
            ->with('product')
            ->get()
            ->groupBy('product_id')
            ->map(function ($services) {
                $product = $services->first()->product;
                return [
                    'product_name' => $product->name ?? 'Unknown',
                    'count' => $services->count(),
                    'revenue' => $services->sum('price'),
                ];
            })
            ->sortByDesc('revenue')
            ->values();

        // New services per day
        $dailySales = Service::whereBetween('created_at', [$startDate, $endDate])
            ->selectRaw('DATE(created_at) as date, COUNT(*) as count, SUM(price) as revenue')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Summary
        $summary = [
            'total_sales' => Service::whereBetween('created_at', [$startDate, $endDate])->count(),
            'total_revenue' => Service::whereBetween('created_at', [$startDate, $endDate])->sum('price'),
            'avg_sale_value' => Service::whereBetween('created_at', [$startDate, $endDate])->avg('price'),
        ];

        return view('admin.reports.sales', compact('salesByProduct', 'dailySales', 'summary', 'startDate', 'endDate'));
    }

    /**
     * Services report
     */
    public function services(Request $request)
    {
        // Status breakdown
        $statusBreakdown = Service::selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->get()
            ->pluck('count', 'status')
            ->toArray();

        // Billing cycle breakdown
        $billingBreakdown = Service::selectRaw('billing_cycle, COUNT(*) as count, SUM(price) as mrr')
            ->where('status', 'active')
            ->groupBy('billing_cycle')
            ->get();

        // Churned services (terminated in last 30 days)
        $churnedServices = Service::where('status', 'terminated')
            ->where('terminated_at', '>=', Carbon::now()->subDays(30))
            ->count();

        // MRR calculation
        $mrr = Service::where('status', 'active')
            ->get()
            ->sum(function ($service) {
                return match($service->billing_cycle) {
                    'monthly' => $service->price,
                    'quarterly' => $service->price / 3,
                    'semi_annually' => $service->price / 6,
                    'annually' => $service->price / 12,
                    'biennially' => $service->price / 24,
                    'triennially' => $service->price / 36,
                    default => 0,
                };
            });

        // Summary
        $summary = [
            'total_services' => Service::count(),
            'active_services' => Service::where('status', 'active')->count(),
            'suspended_services' => Service::where('status', 'suspended')->count(),
            'mrr' => $mrr,
            'arr' => $mrr * 12,
            'churn_rate' => Service::where('status', 'active')->count() > 0 
                ? ($churnedServices / Service::where('status', 'active')->count()) * 100 
                : 0,
        ];

        return view('admin.reports.services', compact('statusBreakdown', 'billingBreakdown', 'summary'));
    }

    /**
     * Clients report
     */
    public function clients(Request $request)
    {
        $startDate = $request->input('start_date', Carbon::now()->subDays(30));
        $endDate = $request->input('end_date', Carbon::now());

        // New clients
        $newClients = User::whereBetween('created_at', [$startDate, $endDate])
            ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Top clients by revenue
        $topClients = Client::with('user')
            ->withSum(['invoices' => function ($query) {
                $query->where('status', 'paid');
            }], 'total')
            ->orderByDesc('invoices_sum_total')
            ->limit(10)
            ->get();

        // Summary
        $summary = [
            'total_clients' => Client::count(),
            'active_clients' => Client::whereHas('services', function ($q) {
                $q->where('status', 'active');
            })->count(),
            'new_this_month' => User::where('created_at', '>=', Carbon::now()->startOfMonth())->count(),
        ];

        return view('admin.reports.clients', compact('newClients', 'topClients', 'summary', 'startDate', 'endDate'));
    }

    /**
     * Export report to CSV
     */
    public function export(Request $request)
    {
        $type = $request->input('type', 'revenue');
        $startDate = $request->input('start_date', Carbon::now()->subDays(30));
        $endDate = $request->input('end_date', Carbon::now());

        $filename = "{$type}_report_" . date('Y-m-d') . ".csv";

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function() use ($type, $startDate, $endDate) {
            $file = fopen('php://output', 'w');

            if ($type === 'revenue') {
                fputcsv($file, ['Date', 'Amount', 'Gateway', 'Transaction ID']);

                Payment::where('status', 'success')
                    ->whereBetween('created_at', [$startDate, $endDate])
                    ->orderBy('created_at')
                    ->chunk(100, function ($payments) use ($file) {
                        foreach ($payments as $payment) {
                            fputcsv($file, [
                                $payment->created_at->format('Y-m-d'),
                                $payment->amount,
                                $payment->gateway,
                                $payment->transaction_id,
                            ]);
                        }
                    });
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
