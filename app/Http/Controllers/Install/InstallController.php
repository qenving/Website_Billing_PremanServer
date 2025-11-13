<?php

namespace App\Http\Controllers\Install;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class InstallController extends Controller
{
    public function index()
    {
        // Check if already installed
        if (config('hbm.installed')) {
            return redirect('/');
        }

        return view('install.welcome');
    }

    public function requirements()
    {
        $requirements = [
            'php_version' => [
                'name' => 'PHP Version >= 8.2',
                'check' => version_compare(PHP_VERSION, '8.2.0', '>='),
                'value' => PHP_VERSION,
            ],
            'openssl' => [
                'name' => 'OpenSSL Extension',
                'check' => extension_loaded('openssl'),
                'value' => extension_loaded('openssl') ? 'Enabled' : 'Disabled',
            ],
            'pdo' => [
                'name' => 'PDO Extension',
                'check' => extension_loaded('pdo'),
                'value' => extension_loaded('pdo') ? 'Enabled' : 'Disabled',
            ],
            'mbstring' => [
                'name' => 'Mbstring Extension',
                'check' => extension_loaded('mbstring'),
                'value' => extension_loaded('mbstring') ? 'Enabled' : 'Disabled',
            ],
            'tokenizer' => [
                'name' => 'Tokenizer Extension',
                'check' => extension_loaded('tokenizer'),
                'value' => extension_loaded('tokenizer') ? 'Enabled' : 'Disabled',
            ],
            'json' => [
                'name' => 'JSON Extension',
                'check' => extension_loaded('json'),
                'value' => extension_loaded('json') ? 'Enabled' : 'Disabled',
            ],
            'curl' => [
                'name' => 'cURL Extension',
                'check' => extension_loaded('curl'),
                'value' => extension_loaded('curl') ? 'Enabled' : 'Disabled',
            ],
            'zip' => [
                'name' => 'ZIP Extension',
                'check' => extension_loaded('zip'),
                'value' => extension_loaded('zip') ? 'Enabled' : 'Disabled',
            ],
        ];

        $permissions = [
            'storage' => [
                'name' => 'storage/',
                'check' => is_writable(storage_path()),
                'path' => storage_path(),
            ],
            'bootstrap_cache' => [
                'name' => 'bootstrap/cache/',
                'check' => is_writable(base_path('bootstrap/cache')),
                'path' => base_path('bootstrap/cache'),
            ],
            'env' => [
                'name' => '.env',
                'check' => is_writable(base_path('.env')),
                'path' => base_path('.env'),
            ],
        ];

        return view('install.requirements', compact('requirements', 'permissions'));
    }

    public function database()
    {
        return view('install.database');
    }

    public function databaseStore(Request $request)
    {
        $request->validate([
            'db_host' => 'required',
            'db_port' => 'required|numeric',
            'db_database' => 'required',
            'db_username' => 'required',
            'db_password' => 'nullable',
        ]);

        // Test database connection
        try {
            $pdo = new \PDO(
                "mysql:host={$request->db_host};port={$request->db_port};dbname={$request->db_database}",
                $request->db_username,
                $request->db_password
            );
        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Database connection failed: ' . $e->getMessage());
        }

        // Update .env file
        $this->updateEnvFile([
            'DB_HOST' => $request->db_host,
            'DB_PORT' => $request->db_port,
            'DB_DATABASE' => $request->db_database,
            'DB_USERNAME' => $request->db_username,
            'DB_PASSWORD' => $request->db_password,
        ]);

        // Run migrations
        try {
            Artisan::call('migrate', ['--force' => true]);
            Artisan::call('db:seed', ['--force' => true]);
        } catch (\Exception $e) {
            return back()->with('error', 'Migration failed: ' . $e->getMessage());
        }

        return redirect()->route('install.admin');
    }

    public function admin()
    {
        return view('install.admin');
    }

    public function adminStore(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:8|confirmed',
        ]);

        // Create super admin user
        $user = \App\Models\User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
            'is_active' => true,
        ]);

        // Assign super admin role
        $role = \App\Models\Role::where('name', 'super_admin')->first();
        if ($role) {
            $user->role_id = $role->id;
            $user->save();
        }

        return redirect()->route('install.complete');
    }

    public function complete()
    {
        // Mark as installed
        $this->updateEnvFile(['HBM_INSTALLED' => 'true']);

        Artisan::call('config:clear');
        Artisan::call('cache:clear');

        return view('install.complete');
    }

    protected function updateEnvFile(array $data)
    {
        $envFile = base_path('.env');
        $content = File::get($envFile);

        foreach ($data as $key => $value) {
            $value = str_replace('"', '\"', $value);

            if (preg_match("/^{$key}=/m", $content)) {
                $content = preg_replace("/^{$key}=.*/m", "{$key}=\"{$value}\"", $content);
            } else {
                $content .= "\n{$key}=\"{$value}\"";
            }
        }

        File::put($envFile, $content);
    }
}
