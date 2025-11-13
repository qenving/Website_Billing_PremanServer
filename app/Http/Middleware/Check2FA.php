<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class Check2FA
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();

        // Skip 2FA check for 2FA verification routes
        if ($request->is('2fa/verify') || $request->is('2fa/verify/*')) {
            return $next($request);
        }

        // Check if user has 2FA enabled but not verified in current session
        if ($user && $user->hasTwoFactorEnabled() && !session('2fa_verified')) {
            return redirect()->route('2fa.verify');
        }

        // Check if 2FA is required for admin users
        if ($user && $user->isAdmin() && config('hbm.require_2fa_admin') && !$user->hasTwoFactorEnabled()) {
            session()->flash('warning', '2FA is required for admin users. Please enable it in your account settings.');
        }

        return $next($request);
    }
}
