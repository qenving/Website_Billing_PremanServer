<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Priority order:
        // 1. URL parameter (?lang=id)
        // 2. Authenticated user's language preference
        // 3. Session language
        // 4. Default application language

        $locale = null;

        // Check URL parameter
        if ($request->has('lang')) {
            $locale = $request->get('lang');
            Session::put('locale', $locale);
        }
        // Check authenticated user's preference
        elseif (auth()->check() && auth()->user()->language) {
            $locale = auth()->user()->language;
        }
        // Check session
        elseif (Session::has('locale')) {
            $locale = Session::get('locale');
        }

        // Validate and set locale
        if ($locale && in_array($locale, config('app.supported_locales', ['en']))) {
            App::setLocale($locale);
        }

        return $next($request);
    }
}
