<?php

namespace App\Http\Middleware;

use App\Models\ThemeSetting;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class LoadThemeSettings
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (config('hbm.installed')) {
            $theme = ThemeSetting::first();
            if ($theme) {
                view()->share('themeSettings', $theme);
            }
        }

        return $next($request);
    }
}
