<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class LanguageController extends Controller
{
    /**
     * Switch the application language.
     */
    public function switch(Request $request, string $locale)
    {
        // Validate locale
        if (!in_array($locale, config('app.supported_locales', ['en']))) {
            return redirect()->back()->with('error', 'Unsupported language');
        }

        // Store in session
        Session::put('locale', $locale);

        // Update user preference if authenticated
        if (Auth::check()) {
            Auth::user()->update(['language' => $locale]);
        }

        return redirect()->back()->with('success', 'Language changed successfully');
    }
}
