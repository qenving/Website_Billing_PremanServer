<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;

class SettingsController extends Controller
{
    public function index()
    {
        $settings = Setting::all()->groupBy('group');

        return view('admin.settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'settings' => 'required|array',
            'settings.*' => 'nullable|string|max:1000',
        ]);

        try {
            foreach ($validated['settings'] as $key => $value) {
                Setting::updateOrCreate(
                    ['key' => $key],
                    ['value' => $value ?? '']
                );
            }

            // Clear cache
            Cache::flush();

            // Update .env if needed for critical settings
            if ($request->has('settings.app_url')) {
                $this->updateEnvFile('APP_URL', $request->input('settings.app_url'));
            }

            // Audit log
            AuditLog::create([
                'user_id' => auth()->id(),
                'action' => 'settings.updated',
                'description' => 'Updated system settings',
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            return back()->with('success', 'Settings updated successfully.');

        } catch (\Exception $e) {
            return back()->with('error', 'Failed to update settings: ' . $e->getMessage());
        }
    }

    protected function updateEnvFile($key, $value)
    {
        $envFile = base_path('.env');

        if (!file_exists($envFile)) {
            return;
        }

        $content = file_get_contents($envFile);

        // Escape special characters in value
        $value = str_replace('"', '\"', $value);

        // Check if key exists
        if (preg_match("/^{$key}=.*/m", $content)) {
            // Replace existing
            $content = preg_replace("/^{$key}=.*/m", "{$key}=\"{$value}\"", $content);
        } else {
            // Add new
            $content .= "\n{$key}=\"{$value}\"\n";
        }

        file_put_contents($envFile, $content);

        // Clear config cache
        if (function_exists('opcache_reset')) {
            opcache_reset();
        }

        Artisan::call('config:clear');
    }
}
