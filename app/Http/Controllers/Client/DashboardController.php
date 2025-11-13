<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Service;
use App\Models\Ticket;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $client = Auth::user()->client;

        // Client statistics
        $stats = [
            'active_services' => Service::where('client_id', $client->id)
                ->where('status', 'active')
                ->count(),
            'total_services' => Service::where('client_id', $client->id)->count(),
            'unpaid_invoices' => Invoice::where('client_id', $client->id)
                ->where('status', 'unpaid')
                ->count(),
            'unpaid_amount' => Invoice::where('client_id', $client->id)
                ->where('status', 'unpaid')
                ->sum('total'),
            'open_tickets' => Ticket::where('client_id', $client->id)
                ->whereIn('status', ['open', 'on_hold', 'answered'])
                ->count(),
            'credit_balance' => $client->credit_balance,
        ];

        // Recent services (up to 5)
        $recentServices = Service::where('client_id', $client->id)
            ->with('product')
            ->latest()
            ->take(5)
            ->get();

        // Unpaid invoices (up to 5)
        $unpaidInvoices = Invoice::where('client_id', $client->id)
            ->where('status', 'unpaid')
            ->latest()
            ->take(5)
            ->get();

        // Recent tickets (up to 5)
        $recentTickets = Ticket::where('client_id', $client->id)
            ->with('department')
            ->latest()
            ->take(5)
            ->get();

        // Recent payments (up to 5)
        $recentPayments = Payment::whereHas('invoice', function($q) use ($client) {
            $q->where('client_id', $client->id);
        })->with(['invoice', 'gateway'])
            ->latest()
            ->take(5)
            ->get();

        // Service status breakdown
        $servicesByStatus = Service::where('client_id', $client->id)
            ->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->get()
            ->pluck('count', 'status')
            ->toArray();

        // Monthly spending (last 6 months)
        $monthlySpending = Payment::whereHas('invoice', function($q) use ($client) {
            $q->where('client_id', $client->id);
        })->where('status', 'completed')
            ->where('created_at', '>', now()->subMonths(6))
            ->selectRaw('DATE_FORMAT(created_at, "%Y-%m") as month, SUM(amount) as total')
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        return view('client.dashboard.index', compact(
            'client',
            'stats',
            'recentServices',
            'unpaidInvoices',
            'recentTickets',
            'recentPayments',
            'servicesByStatus',
            'monthlySpending'
        ));
    }

    public function notifications()
    {
        $client = Auth::user()->client;

        // Get various notifications
        $notifications = [
            'expiring_services' => Service::where('client_id', $client->id)
                ->where('status', 'active')
                ->whereNotNull('next_due_date')
                ->where('next_due_date', '<=', now()->addDays(7))
                ->with('product')
                ->get(),

            'overdue_invoices' => Invoice::where('client_id', $client->id)
                ->where('status', 'unpaid')
                ->where('due_date', '<', now())
                ->get(),

            'suspended_services' => Service::where('client_id', $client->id)
                ->where('status', 'suspended')
                ->with('product')
                ->get(),

            'answered_tickets' => Ticket::where('client_id', $client->id)
                ->where('status', 'answered')
                ->whereNull('client_read_at')
                ->with('department')
                ->get(),
        ];

        return view('client.dashboard.notifications', compact('notifications'));
    }
}
