<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Client;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Service;
use App\Models\Ticket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class ClientController extends Controller
{
    public function index(Request $request)
    {
        $query = Client::with('user');

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('user', function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            })->orWhere('company_name', 'like', "%{$search}%")
              ->orWhere('company_phone', 'like', "%{$search}%");
        }

        // Filter by status
        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->whereHas('user', fn($q) => $q->where('is_active', true));
            } elseif ($request->status === 'inactive') {
                $query->whereHas('user', fn($q) => $q->where('is_active', false));
            }
        }

        $clients = $query->latest()->paginate(20);

        return view('admin.clients.index', compact('clients'));
    }

    public function show(Client $client)
    {
        $client->load('user');

        // Get client statistics
        $stats = [
            'services' => Service::where('client_id', $client->id)->count(),
            'active_services' => Service::where('client_id', $client->id)->where('status', 'active')->count(),
            'total_spent' => Payment::whereHas('invoice', function($q) use ($client) {
                $q->where('client_id', $client->id);
            })->where('status', 'completed')->sum('amount'),
            'pending_invoices' => Invoice::where('client_id', $client->id)->where('status', 'unpaid')->count(),
            'open_tickets' => Ticket::where('client_id', $client->id)->whereIn('status', ['open', 'on_hold', 'answered'])->count(),
        ];

        // Recent activity
        $recentServices = Service::where('client_id', $client->id)
            ->with('product')
            ->latest()
            ->take(5)
            ->get();

        $recentInvoices = Invoice::where('client_id', $client->id)
            ->latest()
            ->take(5)
            ->get();

        $recentPayments = Payment::whereHas('invoice', function($q) use ($client) {
            $q->where('client_id', $client->id);
        })->with('invoice', 'gateway')
            ->latest()
            ->take(5)
            ->get();

        $recentTickets = Ticket::where('client_id', $client->id)
            ->with('department')
            ->latest()
            ->take(5)
            ->get();

        return view('admin.clients.show', compact(
            'client',
            'stats',
            'recentServices',
            'recentInvoices',
            'recentPayments',
            'recentTickets'
        ));
    }

    public function edit(Client $client)
    {
        $client->load('user');
        return view('admin.clients.edit', compact('client'));
    }

    public function update(Request $request, Client $client)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $client->user_id,
            'company_name' => 'nullable|string|max:255',
            'company_phone' => 'nullable|string|max:20',
            'company_address' => 'nullable|string|max:500',
            'credit_balance' => 'nullable|numeric|min:0',
            'password' => 'nullable|string|min:8',
        ]);

        DB::beginTransaction();

        try {
            // Update user
            $userData = [
                'name' => $request->name,
                'email' => $request->email,
            ];

            if ($request->filled('password')) {
                $userData['password'] = Hash::make($request->password);
            }

            $client->user->update($userData);

            // Update client
            $client->update([
                'company_name' => $request->company_name,
                'company_phone' => $request->company_phone,
                'company_address' => $request->company_address,
                'credit_balance' => $request->credit_balance ?? 0,
            ]);

            DB::commit();

            // Audit log
            AuditLog::create([
                'user_id' => auth()->id(),
                'action' => 'client.updated',
                'description' => "Updated client: {$client->user->name} (ID: {$client->id})",
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            return redirect()->route('admin.clients.show', $client)
                ->with('success', 'Client updated successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Failed to update client: ' . $e->getMessage());
        }
    }

    public function toggleStatus(Client $client)
    {
        $user = $client->user;
        $user->update(['is_active' => !$user->is_active]);

        $status = $user->is_active ? 'activated' : 'deactivated';

        // Audit log
        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => "client.{$status}",
            'description' => "Client {$status}: {$user->name} (ID: {$client->id})",
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        return back()->with('success', "Client {$status} successfully.");
    }

    public function loginAsClient(Client $client)
    {
        // Security check - only super admins can use this feature
        if (!auth()->user()->role->name === 'super_admin') {
            return back()->with('error', 'Unauthorized action.');
        }

        // Check if client is active
        if (!$client->user->is_active) {
            return back()->with('error', 'Cannot login as inactive client.');
        }

        // Store admin ID in session for returning later
        session(['impersonating_admin_id' => auth()->id()]);

        // Login as client
        auth()->login($client->user);

        // Audit log
        AuditLog::create([
            'user_id' => session('impersonating_admin_id'),
            'action' => 'client.impersonated',
            'description' => "Logged in as client: {$client->user->name} (ID: {$client->id})",
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        return redirect()->route('client.dashboard')
            ->with('success', "You are now logged in as {$client->user->name}");
    }

    public function addCredit(Request $request, Client $client)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'description' => 'required|string|max:500',
        ]);

        $oldBalance = $client->credit_balance;
        $newBalance = $oldBalance + $request->amount;

        $client->update(['credit_balance' => $newBalance]);

        // Create credit transaction record
        DB::table('credit_transactions')->insert([
            'client_id' => $client->id,
            'amount' => $request->amount,
            'type' => 'credit',
            'description' => $request->description,
            'balance_before' => $oldBalance,
            'balance_after' => $newBalance,
            'created_by' => auth()->id(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Audit log
        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'client.credit_added',
            'description' => "Added {$request->amount} credit to client: {$client->user->name} (ID: {$client->id})",
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return back()->with('success', 'Credit added successfully.');
    }

    public function deductCredit(Request $request, Client $client)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0.01|max:' . $client->credit_balance,
            'description' => 'required|string|max:500',
        ]);

        $oldBalance = $client->credit_balance;
        $newBalance = $oldBalance - $request->amount;

        $client->update(['credit_balance' => $newBalance]);

        // Create credit transaction record
        DB::table('credit_transactions')->insert([
            'client_id' => $client->id,
            'amount' => $request->amount,
            'type' => 'debit',
            'description' => $request->description,
            'balance_before' => $oldBalance,
            'balance_after' => $newBalance,
            'created_by' => auth()->id(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Audit log
        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'client.credit_deducted',
            'description' => "Deducted {$request->amount} credit from client: {$client->user->name} (ID: {$client->id})",
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return back()->with('success', 'Credit deducted successfully.');
    }

    public function sendEmail(Request $request, Client $client)
    {
        $request->validate([
            'subject' => 'required|string|max:255',
            'message' => 'required|string|min:10',
        ]);

        try {
            \Mail::send('emails.admin-to-client', [
                'clientName' => $client->user->name,
                'messageContent' => $request->message,
            ], function($mail) use ($client, $request) {
                $mail->to($client->user->email)
                     ->subject($request->subject);
            });

            // Audit log
            AuditLog::create([
                'user_id' => auth()->id(),
                'action' => 'client.email_sent',
                'description' => "Sent email to client: {$client->user->name} - Subject: {$request->subject}",
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            return back()->with('success', 'Email sent successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to send email: ' . $e->getMessage());
        }
    }
}
