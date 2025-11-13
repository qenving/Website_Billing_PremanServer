<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckInstallation
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Skip check for install routes
        if ($request->is('install') || $request->is('install/*')) {
            return $next($request);
        }

        // Check if application is installed
        if (!config('hbm.installed')) {
            return redirect('/install');
        }

        return $next($request);
    }
}
