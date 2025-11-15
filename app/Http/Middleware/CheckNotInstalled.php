<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Symfony\Component\HttpFoundation\Response;

class CheckNotInstalled
{
    /**
     * Handle an incoming request.
     * Block installer routes if already installed.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $lockFile = storage_path('installed');

        // If already installed, redirect to login
        if (File::exists($lockFile)) {
            return redirect('/login')->with('error', 'Application is already installed.');
        }

        return $next($request);
    }
}
