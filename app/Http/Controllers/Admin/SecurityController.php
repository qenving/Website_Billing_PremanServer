<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\LoginAttempt;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class SecurityController extends Controller
{
    public function auditLogs(Request $request)
    {
        $query = AuditLog::with('user');

        // Filter by user
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        // Filter by action
        if ($request->filled('action')) {
            $query->where('action', 'like', "%{$request->action}%");
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Search by description or IP
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                  ->orWhere('ip_address', 'like', "%{$search}%");
            });
        }

        $logs = $query->latest()->paginate(50);
        $users = \App\Models\User::all();

        // Get action types for filter
        $actionTypes = AuditLog::select('action')
            ->distinct()
            ->orderBy('action')
            ->pluck('action');

        return view('admin.security.audit-logs', compact('logs', 'users', 'actionTypes'));
    }

    public function loginAttempts(Request $request)
    {
        $query = LoginAttempt::with('user');

        // Filter by success/failure
        if ($request->filled('successful')) {
            $query->where('successful', $request->successful === 'true');
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Search by email or IP
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('email', 'like', "%{$search}%")
                  ->orWhere('ip_address', 'like', "%{$search}%");
            });
        }

        $attempts = $query->latest()->paginate(50);

        // Get statistics
        $stats = [
            'total_attempts' => LoginAttempt::count(),
            'successful_attempts' => LoginAttempt::where('successful', true)->count(),
            'failed_attempts' => LoginAttempt::where('successful', false)->count(),
            'unique_ips' => LoginAttempt::distinct('ip_address')->count(),
            'blocked_ips' => $this->getBlockedIps()->count(),
        ];

        return view('admin.security.login-attempts', compact('attempts', 'stats'));
    }

    public function blockedIps()
    {
        $blockedIps = $this->getBlockedIps();

        return view('admin.security.blocked-ips', compact('blockedIps'));
    }

    public function blockIp(Request $request)
    {
        $request->validate([
            'ip_address' => 'required|ip',
            'reason' => 'required|string|max:500',
            'expires_at' => 'nullable|date|after:now',
        ]);

        $blockedIps = Cache::get('blocked_ips', []);

        $blockedIps[$request->ip_address] = [
            'reason' => $request->reason,
            'blocked_at' => now()->toDateTimeString(),
            'blocked_by' => auth()->id(),
            'expires_at' => $request->expires_at,
        ];

        Cache::forever('blocked_ips', $blockedIps);

        // Audit log
        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'security.ip_blocked',
            'description' => "Blocked IP address: {$request->ip_address} - Reason: {$request->reason}",
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return back()->with('success', 'IP address blocked successfully.');
    }

    public function unblockIp(Request $request)
    {
        $request->validate([
            'ip_address' => 'required|ip',
        ]);

        $blockedIps = Cache::get('blocked_ips', []);

        if (isset($blockedIps[$request->ip_address])) {
            unset($blockedIps[$request->ip_address]);
            Cache::forever('blocked_ips', $blockedIps);

            // Audit log
            AuditLog::create([
                'user_id' => auth()->id(),
                'action' => 'security.ip_unblocked',
                'description' => "Unblocked IP address: {$request->ip_address}",
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            return back()->with('success', 'IP address unblocked successfully.');
        }

        return back()->with('error', 'IP address not found in blocked list.');
    }

    public function twoFactorSettings()
    {
        $settings = [
            'enforce_2fa_admin' => \App\Models\Setting::where('key', 'enforce_2fa_admin')->value('value') === 'true',
            'enforce_2fa_client' => \App\Models\Setting::where('key', 'enforce_2fa_client')->value('value') === 'true',
            'session_timeout' => (int) \App\Models\Setting::where('key', 'session_timeout')->value('value') ?? 120,
            'password_expiry_days' => (int) \App\Models\Setting::where('key', 'password_expiry_days')->value('value') ?? 90,
        ];

        return view('admin.security.2fa-settings', compact('settings'));
    }

    public function updateTwoFactorSettings(Request $request)
    {
        $request->validate([
            'enforce_2fa_admin' => 'boolean',
            'enforce_2fa_client' => 'boolean',
            'session_timeout' => 'required|integer|min:5|max:1440',
            'password_expiry_days' => 'required|integer|min:0|max:365',
        ]);

        $settings = [
            'enforce_2fa_admin' => $request->boolean('enforce_2fa_admin') ? 'true' : 'false',
            'enforce_2fa_client' => $request->boolean('enforce_2fa_client') ? 'true' : 'false',
            'session_timeout' => $request->session_timeout,
            'password_expiry_days' => $request->password_expiry_days,
        ];

        foreach ($settings as $key => $value) {
            \App\Models\Setting::updateOrCreate(
                ['key' => $key],
                ['value' => $value]
            );
        }

        // Audit log
        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'security.2fa_settings_updated',
            'description' => 'Updated 2FA and security settings',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return back()->with('success', 'Security settings updated successfully.');
    }

    public function suspiciousActivity(Request $request)
    {
        // Multiple failed login attempts from same IP
        $suspiciousLogins = LoginAttempt::select('ip_address')
            ->where('successful', false)
            ->where('created_at', '>', now()->subHour())
            ->groupBy('ip_address')
            ->havingRaw('COUNT(*) >= 5')
            ->get();

        // Multiple failed login attempts with same email
        $suspiciousEmails = LoginAttempt::select('email')
            ->where('successful', false)
            ->where('created_at', '>', now()->subHour())
            ->groupBy('email')
            ->havingRaw('COUNT(*) >= 5')
            ->get();

        // Recently created accounts with no activity
        $inactiveNewAccounts = \App\Models\User::where('created_at', '>', now()->subDays(7))
            ->whereDoesntHave('auditLogs')
            ->get();

        // High-value transactions
        $highValuePayments = \App\Models\Payment::where('amount', '>', 1000)
            ->where('created_at', '>', now()->subDay())
            ->with(['invoice.client.user', 'gateway'])
            ->get();

        return view('admin.security.suspicious-activity', compact(
            'suspiciousLogins',
            'suspiciousEmails',
            'inactiveNewAccounts',
            'highValuePayments'
        ));
    }

    public function exportAuditLogs(Request $request)
    {
        $request->validate([
            'date_from' => 'required|date',
            'date_to' => 'required|date|after_or_equal:date_from',
        ]);

        $logs = AuditLog::with('user')
            ->whereBetween('created_at', [$request->date_from, $request->date_to])
            ->get();

        $filename = 'audit_logs_' . date('Y-m-d_His') . '.csv';

        $callback = function() use ($logs) {
            $file = fopen('php://output', 'w');

            // Headers
            fputcsv($file, [
                'ID',
                'User',
                'Action',
                'Description',
                'IP Address',
                'User Agent',
                'Created At',
            ]);

            // Data
            foreach ($logs as $log) {
                fputcsv($file, [
                    $log->id,
                    $log->user?->name ?? 'System',
                    $log->action,
                    $log->description,
                    $log->ip_address,
                    $log->user_agent,
                    $log->created_at->format('Y-m-d H:i:s'),
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ]);
    }

    protected function getBlockedIps()
    {
        $blockedIps = Cache::get('blocked_ips', []);

        // Remove expired blocks
        $now = now();
        foreach ($blockedIps as $ip => $data) {
            if (isset($data['expires_at']) && $data['expires_at'] && $now->greaterThan($data['expires_at'])) {
                unset($blockedIps[$ip]);
            }
        }

        Cache::forever('blocked_ips', $blockedIps);

        return collect($blockedIps);
    }
}
