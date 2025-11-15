<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use App\Models\User;
use App\Models\Role;

class InstallController extends Controller
{
    public function index()
    {
        return view('install.welcome');
    }

    public function requirements()
    {
        return view('install.requirements');
    }

    public function database()
    {
        return view('install.database');
    }

    public function databaseStore(Request $request)
    {
        $validated = $request->validate([
            'db_host' => 'required',
            'db_port' => 'required',
            'db_name' => 'required',
            'db_username' => 'required',
            'db_password' => 'nullable',
        ]);

        // Update .env file
        $this->updateEnv([
            'DB_HOST' => $validated['db_host'],
            'DB_PORT' => $validated['db_port'],
            'DB_DATABASE' => $validated['db_name'],
            'DB_USERNAME' => $validated['db_username'],
            'DB_PASSWORD' => $validated['db_password'] ?? '',
        ]);

        return redirect()->route('install.database.confirm');
    }

    public function databaseConfirm()
    {
        try {
            DB::connection()->getPdo();
            return view('install.database-confirm');
        } catch (\Exception $e) {
            return redirect()->route('install.database')
                ->withErrors(['error' => 'Could not connect to database: ' . $e->getMessage()]);
        }
    }

    public function databaseReset(Request $request)
    {
        try {
            Artisan::call('migrate:fresh');
            Artisan::call('db:seed');

            return redirect()->route('install.admin');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Migration failed: ' . $e->getMessage()]);
        }
    }

    public function admin()
    {
        return view('install.admin');
    }

    public function adminStore(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:8|confirmed',
        ]);

        try {
            // Create super admin role if doesn't exist
            $role = Role::firstOrCreate(
                ['name' => 'super_admin'],
                ['description' => 'Super Administrator with full access']
            );

            // Create admin user
            User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => bcrypt($validated['password']),
                'role_id' => $role->id,
                'email_verified_at' => now(),
                'is_active' => true,
            ]);

            return redirect()->route('install.smtp');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Failed to create admin: ' . $e->getMessage()]);
        }
    }

    /**
     * Show SMTP configuration form
     */
    public function smtp()
    {
        return view('install.smtp');
    }

    /**
     * Save SMTP configuration
     */
    public function smtpStore(Request $request)
    {
        $validated = $request->validate([
            'mail_driver' => 'required|in:smtp,sendmail,log',
            'mail_host' => 'nullable|string',
            'mail_port' => 'nullable|integer',
            'mail_username' => 'nullable|string',
            'mail_password' => 'nullable|string',
            'mail_encryption' => 'nullable|in:tls,ssl,',
            'mail_from_address' => 'nullable|email',
            'mail_from_name' => 'nullable|string',
        ]);

        try {
            // Update .env file with SMTP settings
            $envUpdates = [
                'MAIL_MAILER' => $validated['mail_driver'],
            ];

            if ($validated['mail_driver'] === 'smtp') {
                $envUpdates['MAIL_HOST'] = $validated['mail_host'] ?? '';
                $envUpdates['MAIL_PORT'] = $validated['mail_port'] ?? 587;
                $envUpdates['MAIL_USERNAME'] = $validated['mail_username'] ?? '';
                $envUpdates['MAIL_PASSWORD'] = $validated['mail_password'] ?? '';
                $envUpdates['MAIL_ENCRYPTION'] = $validated['mail_encryption'] ?? 'tls';
            }

            $envUpdates['MAIL_FROM_ADDRESS'] = $validated['mail_from_address'] ?? 'noreply@' . request()->getHost();
            $envUpdates['MAIL_FROM_NAME'] = $validated['mail_from_name'] ?? config('app.name');

            $this->updateEnv($envUpdates);

            // Clear config cache
            Artisan::call('config:clear');

            return redirect()->route('install.complete');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Failed to save SMTP settings: ' . $e->getMessage()]);
        }
    }

    public function complete()
    {
        // Mark installation as complete
        $this->updateEnv(['HBM_INSTALLED' => 'true']);

        return view('install.complete');
    }

    /**
     * Update .env file
     */
    private function updateEnv(array $data)
    {
        $envFile = base_path('.env');

        if (!File::exists($envFile)) {
            File::copy(base_path('.env.example'), $envFile);
        }

        $envContent = File::get($envFile);

        foreach ($data as $key => $value) {
            // Escape special characters in value
            $value = str_replace('"', '\"', $value);
            $value = str_replace('$', '\$', $value);

            // Check if key exists
            if (preg_match("/^{$key}=/m", $envContent)) {
                // Update existing key
                $envContent = preg_replace(
                    "/^{$key}=.*/m",
                    "{$key}=\"{$value}\"",
                    $envContent
                );
            } else {
                // Add new key
                $envContent .= "\n{$key}=\"{$value}\"";
            }
        }

        File::put($envFile, $envContent);
    }
}
