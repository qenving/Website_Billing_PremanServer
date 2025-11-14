<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    /**
     * Display settings page
     */
    public function index()
    {
        $groups = ['general', 'billing', 'email', 'system'];
        $settings = [];

        foreach ($groups as $group) {
            $settings[$group] = Setting::where('group', $group)
                ->orderBy('sort_order')
                ->get();
        }

        return view('admin.settings.index', compact('settings', 'groups'));
    }

    /**
     * Update settings
     */
    public function update(Request $request)
    {
        $validated = $request->validate([
            'settings' => 'required|array',
        ]);

        try {
            foreach ($validated['settings'] as $key => $value) {
                Setting::set($key, $value);
            }

            Setting::clearCache();

            return redirect()
                ->route('admin.settings.index')
                ->with('success', 'Settings updated successfully');

        } catch (\Exception $e) {
            return back()
                ->withErrors(['error' => 'Failed to update settings: ' . $e->getMessage()])
                ->withInput();
        }
    }

    /**
     * Clear cache
     */
    public function clearCache()
    {
        try {
            \Artisan::call('cache:clear');
            \Artisan::call('config:clear');
            \Artisan::call('view:clear');
            \Artisan::call('route:clear');

            Setting::clearCache();

            return redirect()
                ->route('admin.settings.index')
                ->with('success', 'All caches cleared successfully');

        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Failed to clear cache: ' . $e->getMessage()]);
        }
    }
}
