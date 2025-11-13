<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Theme;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class ThemeController extends Controller
{
    public function index()
    {
        $themes = Theme::all();
        $activeTheme = Theme::where('is_active', true)->first();

        return view('admin.themes.index', compact('themes', 'activeTheme'));
    }

    public function customize()
    {
        $theme = Theme::where('is_active', true)->first();

        if (!$theme) {
            // Create default theme if none exists
            $theme = Theme::create([
                'name' => 'Default',
                'is_active' => true,
                'primary_color' => '#3B82F6',
                'secondary_color' => '#10B981',
                'accent_color' => '#F59E0B',
                'background_color' => '#F9FAFB',
                'text_color' => '#111827',
                'logo_path' => null,
                'favicon_path' => null,
                'custom_css' => '',
                'font_family' => 'Inter',
            ]);
        }

        $availableFonts = [
            'Inter' => 'Inter (Default)',
            'Roboto' => 'Roboto',
            'Open Sans' => 'Open Sans',
            'Lato' => 'Lato',
            'Montserrat' => 'Montserrat',
            'Poppins' => 'Poppins',
            'Source Sans Pro' => 'Source Sans Pro',
        ];

        return view('admin.themes.customize', compact('theme', 'availableFonts'));
    }

    public function update(Request $request)
    {
        $theme = Theme::where('is_active', true)->first();

        if (!$theme) {
            return back()->with('error', 'No active theme found.');
        }

        $request->validate([
            'primary_color' => 'required|regex:/^#[0-9A-Fa-f]{6}$/',
            'secondary_color' => 'required|regex:/^#[0-9A-Fa-f]{6}$/',
            'accent_color' => 'required|regex:/^#[0-9A-Fa-f]{6}$/',
            'background_color' => 'required|regex:/^#[0-9A-Fa-f]{6}$/',
            'text_color' => 'required|regex:/^#[0-9A-Fa-f]{6}$/',
            'font_family' => 'required|string|max:100',
            'custom_css' => 'nullable|string|max:10000',
            'logo' => 'nullable|image|max:2048|mimes:png,jpg,jpeg,svg',
            'favicon' => 'nullable|image|max:512|mimes:png,ico',
        ]);

        // Handle logo upload
        if ($request->hasFile('logo')) {
            $logoPath = $request->file('logo')->store('themes/logos', 'public');
            $theme->logo_path = $logoPath;
        }

        // Handle favicon upload
        if ($request->hasFile('favicon')) {
            $faviconPath = $request->file('favicon')->store('themes/favicons', 'public');
            $theme->favicon_path = $faviconPath;
        }

        // Update theme settings
        $theme->update([
            'primary_color' => $request->primary_color,
            'secondary_color' => $request->secondary_color,
            'accent_color' => $request->accent_color,
            'background_color' => $request->background_color,
            'text_color' => $request->text_color,
            'font_family' => $request->font_family,
            'custom_css' => $request->custom_css ?? '',
        ]);

        // Clear theme cache
        Cache::forget('active_theme');

        // Audit log
        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'theme.updated',
            'description' => 'Updated theme customization',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return back()->with('success', 'Theme updated successfully.');
    }

    public function resetToDefault()
    {
        $theme = Theme::where('is_active', true)->first();

        if (!$theme) {
            return back()->with('error', 'No active theme found.');
        }

        $theme->update([
            'primary_color' => '#3B82F6',
            'secondary_color' => '#10B981',
            'accent_color' => '#F59E0B',
            'background_color' => '#F9FAFB',
            'text_color' => '#111827',
            'font_family' => 'Inter',
            'custom_css' => '',
        ]);

        // Clear theme cache
        Cache::forget('active_theme');

        // Audit log
        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'theme.reset',
            'description' => 'Reset theme to default settings',
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        return back()->with('success', 'Theme reset to default successfully.');
    }

    public function preview(Request $request)
    {
        // Return a preview of the theme with the given colors
        $previewData = [
            'primary_color' => $request->primary_color ?? '#3B82F6',
            'secondary_color' => $request->secondary_color ?? '#10B981',
            'accent_color' => $request->accent_color ?? '#F59E0B',
            'background_color' => $request->background_color ?? '#F9FAFB',
            'text_color' => $request->text_color ?? '#111827',
            'font_family' => $request->font_family ?? 'Inter',
        ];

        return view('admin.themes.preview', compact('previewData'));
    }

    public function exportTheme()
    {
        $theme = Theme::where('is_active', true)->first();

        if (!$theme) {
            return back()->with('error', 'No active theme found.');
        }

        $themeData = [
            'name' => $theme->name,
            'primary_color' => $theme->primary_color,
            'secondary_color' => $theme->secondary_color,
            'accent_color' => $theme->accent_color,
            'background_color' => $theme->background_color,
            'text_color' => $theme->text_color,
            'font_family' => $theme->font_family,
            'custom_css' => $theme->custom_css,
        ];

        $filename = 'theme_export_' . date('Y-m-d_His') . '.json';

        // Audit log
        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'theme.exported',
            'description' => 'Exported theme configuration',
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        return response()->json($themeData)
            ->header('Content-Type', 'application/json')
            ->header('Content-Disposition', "attachment; filename=\"$filename\"");
    }

    public function importTheme(Request $request)
    {
        $request->validate([
            'theme_file' => 'required|file|mimes:json|max:1024',
        ]);

        try {
            $content = file_get_contents($request->file('theme_file')->getRealPath());
            $themeData = json_decode($content, true);

            if (!$themeData) {
                throw new \Exception('Invalid theme file format.');
            }

            $theme = Theme::where('is_active', true)->first();

            if (!$theme) {
                return back()->with('error', 'No active theme found.');
            }

            $theme->update([
                'primary_color' => $themeData['primary_color'] ?? '#3B82F6',
                'secondary_color' => $themeData['secondary_color'] ?? '#10B981',
                'accent_color' => $themeData['accent_color'] ?? '#F59E0B',
                'background_color' => $themeData['background_color'] ?? '#F9FAFB',
                'text_color' => $themeData['text_color'] ?? '#111827',
                'font_family' => $themeData['font_family'] ?? 'Inter',
                'custom_css' => $themeData['custom_css'] ?? '',
            ]);

            // Clear theme cache
            Cache::forget('active_theme');

            // Audit log
            AuditLog::create([
                'user_id' => auth()->id(),
                'action' => 'theme.imported',
                'description' => 'Imported theme configuration',
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            return back()->with('success', 'Theme imported successfully.');

        } catch (\Exception $e) {
            return back()->with('error', 'Failed to import theme: ' . $e->getMessage());
        }
    }
}
