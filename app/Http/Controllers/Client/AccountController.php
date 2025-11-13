<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use PragmaRX\Google2FA\Google2FA;

class AccountController extends Controller
{
    public function profile()
    {
        $user = Auth::user();
        $client = $user->client;

        return view('client.account.profile', compact('user', 'client'));
    }

    public function updateProfile(Request $request)
    {
        $user = Auth::user();
        $client = $user->client;

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'company_name' => 'nullable|string|max:255',
            'company_phone' => 'nullable|string|max:20',
            'company_address' => 'nullable|string|max:500',
        ]);

        DB::beginTransaction();

        try {
            // Update user
            $user->update([
                'name' => $request->name,
                'email' => $request->email,
            ]);

            // Update client
            $client->update([
                'company_name' => $request->company_name,
                'company_phone' => $request->company_phone,
                'company_address' => $request->company_address,
            ]);

            DB::commit();

            // Audit log
            AuditLog::create([
                'user_id' => $user->id,
                'action' => 'profile.updated',
                'description' => 'Updated profile information',
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            return back()->with('success', 'Profile updated successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Failed to update profile: ' . $e->getMessage());
        }
    }

    public function security()
    {
        $user = Auth::user();

        // Get recent login attempts
        $recentLogins = \App\Models\LoginAttempt::where('user_id', $user->id)
            ->where('successful', true)
            ->latest()
            ->take(10)
            ->get();

        // Get recent audit logs
        $recentActivity = AuditLog::where('user_id', $user->id)
            ->latest()
            ->take(20)
            ->get();

        return view('client.account.security', compact('user', 'recentLogins', 'recentActivity'));
    }

    public function changePassword(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'current_password' => 'required|string',
            'password' => ['required', 'confirmed', Password::min(8)->mixedCase()->numbers()],
        ]);

        // Verify current password
        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'Current password is incorrect.']);
        }

        // Update password
        $user->update([
            'password' => Hash::make($request->password),
        ]);

        // Audit log
        AuditLog::create([
            'user_id' => $user->id,
            'action' => 'password.changed',
            'description' => 'Changed account password',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return back()->with('success', 'Password changed successfully.');
    }

    public function twoFactor()
    {
        $user = Auth::user();

        // Generate QR code if 2FA not enabled
        $qrCode = null;
        $secret = null;

        if (!$user->hasTwoFactorEnabled()) {
            $google2fa = new Google2FA();
            $secret = $google2fa->generateSecretKey();

            $qrCodeUrl = $google2fa->getQRCodeUrl(
                config('app.name'),
                $user->email,
                $secret
            );

            // Generate QR code using a simple inline SVG or URL
            $qrCode = $qrCodeUrl;

            // Store secret temporarily in session
            session(['2fa_temp_secret' => $secret]);
        }

        return view('client.account.2fa', compact('user', 'qrCode', 'secret'));
    }

    public function enableTwoFactor(Request $request)
    {
        $user = Auth::user();

        if ($user->hasTwoFactorEnabled()) {
            return back()->with('error', '2FA is already enabled.');
        }

        $request->validate([
            'verification_code' => 'required|string|size:6',
        ]);

        $secret = session('2fa_temp_secret');

        if (!$secret) {
            return back()->with('error', '2FA setup session expired. Please try again.');
        }

        // Verify the code
        $google2fa = new Google2FA();
        $valid = $google2fa->verifyKey($secret, $request->verification_code);

        if (!$valid) {
            return back()->withErrors(['verification_code' => 'Invalid verification code.']);
        }

        // Enable 2FA
        $user->update([
            'two_factor_secret' => encrypt($secret),
            'two_factor_enabled' => true,
        ]);

        // Clear temp secret
        session()->forget('2fa_temp_secret');

        // Audit log
        AuditLog::create([
            'user_id' => $user->id,
            'action' => '2fa.enabled',
            'description' => 'Enabled two-factor authentication',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return redirect()->route('client.account.2fa')
            ->with('success', 'Two-factor authentication enabled successfully.');
    }

    public function disableTwoFactor(Request $request)
    {
        $user = Auth::user();

        if (!$user->hasTwoFactorEnabled()) {
            return back()->with('error', '2FA is not enabled.');
        }

        $request->validate([
            'password' => 'required|string',
            'verification_code' => 'required|string|size:6',
        ]);

        // Verify password
        if (!Hash::check($request->password, $user->password)) {
            return back()->withErrors(['password' => 'Password is incorrect.']);
        }

        // Verify 2FA code
        $google2fa = new Google2FA();
        $secret = decrypt($user->two_factor_secret);
        $valid = $google2fa->verifyKey($secret, $request->verification_code);

        if (!$valid) {
            return back()->withErrors(['verification_code' => 'Invalid verification code.']);
        }

        // Disable 2FA
        $user->update([
            'two_factor_secret' => null,
            'two_factor_enabled' => false,
        ]);

        // Audit log
        AuditLog::create([
            'user_id' => $user->id,
            'action' => '2fa.disabled',
            'description' => 'Disabled two-factor authentication',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return back()->with('success', 'Two-factor authentication disabled successfully.');
    }

    public function apiTokens()
    {
        $user = Auth::user();
        $tokens = $user->tokens;

        return view('client.account.api-tokens', compact('tokens'));
    }

    public function createApiToken(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'name' => 'required|string|max:255',
            'abilities' => 'nullable|array',
        ]);

        $token = $user->createToken(
            $request->name,
            $request->abilities ?? ['*']
        );

        // Audit log
        AuditLog::create([
            'user_id' => $user->id,
            'action' => 'api_token.created',
            'description' => "Created API token: {$request->name}",
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return back()->with([
            'success' => 'API token created successfully.',
            'token' => $token->plainTextToken,
        ]);
    }

    public function revokeApiToken(Request $request, $tokenId)
    {
        $user = Auth::user();

        $token = $user->tokens()->where('id', $tokenId)->first();

        if (!$token) {
            return back()->with('error', 'Token not found.');
        }

        $tokenName = $token->name;
        $token->delete();

        // Audit log
        AuditLog::create([
            'user_id' => $user->id,
            'action' => 'api_token.revoked',
            'description' => "Revoked API token: {$tokenName}",
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return back()->with('success', 'API token revoked successfully.');
    }

    public function deleteAccount()
    {
        $user = Auth::user();
        $client = $user->client;

        // Check for active services
        $activeServices = \App\Models\Service::where('client_id', $client->id)
            ->where('status', 'active')
            ->count();

        // Check for unpaid invoices
        $unpaidInvoices = \App\Models\Invoice::where('client_id', $client->id)
            ->where('status', 'unpaid')
            ->count();

        return view('client.account.delete', compact('activeServices', 'unpaidInvoices'));
    }

    public function processDeleteAccount(Request $request)
    {
        $user = Auth::user();
        $client = $user->client;

        $request->validate([
            'password' => 'required|string',
            'confirmation' => 'required|in:DELETE',
        ]);

        // Verify password
        if (!Hash::check($request->password, $user->password)) {
            return back()->withErrors(['password' => 'Password is incorrect.']);
        }

        // Check for active services
        $activeServices = \App\Models\Service::where('client_id', $client->id)
            ->where('status', 'active')
            ->count();

        if ($activeServices > 0) {
            return back()->with('error', 'Cannot delete account with active services. Please cancel all services first.');
        }

        // Check for unpaid invoices
        $unpaidInvoices = \App\Models\Invoice::where('client_id', $client->id)
            ->where('status', 'unpaid')
            ->count();

        if ($unpaidInvoices > 0) {
            return back()->with('error', 'Cannot delete account with unpaid invoices. Please pay or cancel all invoices first.');
        }

        DB::beginTransaction();

        try {
            // Audit log before deletion
            AuditLog::create([
                'user_id' => $user->id,
                'action' => 'account.deleted',
                'description' => 'Account deleted by user',
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            // Soft delete user (assuming you have soft deletes)
            $user->delete();

            DB::commit();

            // Logout
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect('/')
                ->with('success', 'Your account has been deleted successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to delete account: ' . $e->getMessage());
        }
    }

    public function stopImpersonation()
    {
        // Check if currently impersonating
        if (!session()->has('impersonating_admin_id')) {
            return redirect()->route('client.dashboard');
        }

        $adminId = session('impersonating_admin_id');
        $admin = \App\Models\User::find($adminId);

        if (!$admin) {
            session()->forget('impersonating_admin_id');
            return redirect()->route('login');
        }

        // Audit log
        AuditLog::create([
            'user_id' => $adminId,
            'action' => 'impersonation.stopped',
            'description' => 'Stopped impersonating client: ' . Auth::user()->name,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        // Login back as admin
        Auth::login($admin);
        session()->forget('impersonating_admin_id');

        return redirect()->route('admin.dashboard')
            ->with('success', 'Returned to admin account.');
    }
}
