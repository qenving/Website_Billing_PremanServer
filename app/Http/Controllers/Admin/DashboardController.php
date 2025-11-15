<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Service;
use App\Models\Ticket;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        // Financial stats
        $today = now()->startOfDay();
        $thisMonth = now()->startOfMonth();
        $thisYear = now()->startOfYear();

        $stats = [
            'revenue' => [
                'today' => Payment::where('status', 'completed')
                    ->whereDate('created_at', $today)
                    ->sum('amount'),
                'this_month' => Payment::where('status', 'completed')
                    ->whereDate('created_at', '>=', $thisMonth)
                    ->sum('amount'),
                'this_year' => Payment::where('status', 'completed')
                    ->whereDate('created_at', '>=', $thisYear)
                    ->sum('amount'),
            ],
            'invoices' => [
                'paid' => Invoice::where('status', 'paid')->count(),
                'unpaid' => Invoice::where('status', 'unpaid')->count(),
                'overdue' => Invoice::where('status', 'overdue')->count(),
                'total' => Invoice::count(),
            ],
            'services' => [
                'active' => Service::where('status', 'active')->count(),
                'pending' => Service::where('status', 'pending')->count(),
                'suspended' => Service::where('status', 'suspended')->count(),
                'total' => Service::count(),
            ],
            'clients' => [
                'total' => Client::count(),
                'new_this_month' => Client::whereDate('created_at', '>=', $thisMonth)->count(),
            ],
            'tickets' => [
                'open' => Ticket::where('status', 'open')->count(),
                'pending' => Ticket::where('status', 'pending')->count(),
                'total' => Ticket::count(),
            ],
        ];

        // Recent activities
        $recentInvoices = Invoice::with('client.user')
            ->latest()
            ->limit(5)
            ->get();

        $recentPayments = Payment::with('invoice.client.user')
            ->where('status', 'completed')
            ->latest()
            ->limit(5)
            ->get();

        $recentServices = Service::with('client.user', 'product')
            ->latest()
            ->limit(5)
            ->get();

        // Revenue chart data (last 7 days)
        $revenueChart = Payment::where('status', 'completed')
            ->whereDate('created_at', '>=', now()->subDays(7))
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('SUM(amount) as total')
            )
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->pluck('total', 'date');

        return view('admin.dashboard.index', compact(
            'stats',
            'recentInvoices',
            'recentPayments',
            'recentServices',
            'revenueChart'
        ));
    }
}
