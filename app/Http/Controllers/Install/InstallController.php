<?php

namespace App\Http\Controllers\Install;

use App\Helpers\EnvWriter;
use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use App\Services\DatabaseInstaller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class InstallController extends Controller
{
    protected DatabaseInstaller $databaseInstaller;

    public function __construct()
    {
        $this->databaseInstaller = new DatabaseInstaller();
    }

    /**
     * Step 1: Check server requirements
     */
    public function requirements()
    {
        $requirements = $this->checkRequirements();
        return view('install.requirements', compact('requirements'));
    }

    /**
     * Step 2: Select database mode (Local or Remote)
     */
    public function databaseMode()
    {
        return view('install.database-mode');
    }

    /**
     * Store selected database mode
     */
    public function storeDatabaseMode(Request $request)
    {
        $request->validate([
            'mode' => 'required|in:local,remote'
        ]);

        session(['db_mode' => $request->mode]);

        return redirect()->route('install.database.config');
    }

    /**
     * Step 3: Database configuration form
     */
    public function databaseConfig()
    {
        $mode = session('db_mode', 'remote');
        return view('install.database-config', compact('mode'));
    }

    /**
     * AJAX: Test database connection
     */
    public function testDatabase(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'host' => 'required|string',
            'port' => 'required|integer',
            'database' => 'required|string',
            'username' => 'required|string',
            'password' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed: ' . $validator->errors()->first()
            ]);
        }

        $config = [
            'host' => $request->host,
            'port' => $request->port,
            'database' => $request->database,
            'username' => $request->username,
            'password' => $request->password ?? '',
        ];

        $result = $this->databaseInstaller->testConnection($config);

        return response()->json($result);
    }

    /**
     * Install database (create DB for local, validate for remote)
     */
    public function installDatabase(Request $request)
    {
        $mode = session('db_mode', 'remote');

        $rules = [
            'host' => 'required|string',
            'port' => 'required|integer',
            'database' => 'required|string',
            'username' => 'required|string',
            'password' => 'nullable|string',
        ];

        // Add root credentials validation for local mode
        if ($mode === 'local') {
            $rules['root_username'] = 'required|string';
            $rules['root_password'] = 'nullable|string';
        }

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $config = [
            'host' => $request->host,
            'port' => $request->port,
            'database' => $request->database,
            'username' => $request->username,
            'password' => $request->password ?? '',
        ];

        // Install based on mode
        if ($mode === 'local') {
            $config['root_username'] = $request->root_username;
            $config['root_password'] = $request->root_password ?? '';
            $result = $this->databaseInstaller->installLocal($config);
        } else {
            $result = $this->databaseInstaller->installRemote($config);
        }

        if (!$result['success']) {
            return back()->with('error', $result['message'])->withInput();
        }

        // Update .env file with database credentials
        $envData = [
            'DB_CONNECTION' => 'mysql',
            'DB_HOST' => $config['host'],
            'DB_PORT' => $config['port'],
            'DB_DATABASE' => $config['database'],
            'DB_USERNAME' => $config['username'],
            'DB_PASSWORD' => $config['password'],
        ];

        // Generate APP_KEY if not exists
        if (!env('APP_KEY')) {
            $envData['APP_KEY'] = EnvWriter::generateAppKey();
        }

        EnvWriter::updateEnv($envData);

        // Clear config cache to use new database settings
        Artisan::call('config:clear');

        // Run migrations
        $migrationResult = $this->databaseInstaller->runMigrations();

        if (!$migrationResult['success']) {
            return back()->with('error', 'Database configured but migration failed: ' . $migrationResult['message'])->withInput();
        }

        // Store database config in session for next step
        session(['db_configured' => true]);

        return redirect()->route('install.owner')->with('success', $result['message']);
    }

    /**
     * Step 4: Create OWNER account
     */
    public function owner()
    {
        // Check if database is configured
        if (!session('db_configured')) {
            return redirect()->route('install.database.config')
                ->with('error', 'Please configure database first');
        }

        return view('install.owner');
    }

    /**
     * Create OWNER account
     */
    public function createOwner(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        try {
            // Create OWNER role if not exists
            $ownerRole = Role::firstOrCreate(
                ['slug' => 'owner'],
                [
                    'name' => 'Owner',
                    'description' => 'System Owner - Highest level access with complete control',
                    'is_protected' => true,
                    'permissions' => [
                        'users' => ['view', 'create', 'edit', 'delete'],
                        'roles' => ['view', 'create', 'edit', 'delete'],
                        'settings' => ['view', 'edit'],
                        'extensions' => ['view', 'install', 'configure', 'uninstall'],
                        'products' => ['view', 'create', 'edit', 'delete'],
                        'orders' => ['view', 'create', 'edit', 'delete', 'refund'],
                        'invoices' => ['view', 'create', 'edit', 'delete'],
                        'tickets' => ['view', 'reply', 'close', 'delete'],
                        'reports' => ['view', 'export'],
                        'logs' => ['view', 'delete'],
                    ]
                ]
            );

            // Create OWNER user
            $owner = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role_id' => $ownerRole->id,
                'is_owner' => true,
                'email_verified_at' => now(),
            ]);

            // Mark installation as complete
            File::put(storage_path('installed'), json_encode([
                'installed_at' => now()->toDateTimeString(),
                'version' => config('app.version', '1.0.0'),
                'owner_email' => $owner->email,
            ]));

            // Update .env with installation flag
            EnvWriter::updateEnv([
                'HBM_INSTALLED' => 'true',
                'APP_ENV' => 'production',
                'APP_DEBUG' => 'false',
            ]);

            // Clear all caches
            Artisan::call('config:clear');
            Artisan::call('cache:clear');
            Artisan::call('route:clear');
            Artisan::call('view:clear');

            // Store owner info in session
            session(['owner_created' => true, 'owner_email' => $owner->email]);

            return redirect()->route('install.finish');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to create owner account: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Step 5: Installation complete
     */
    public function finish()
    {
        if (!session('owner_created')) {
            return redirect()->route('install.owner')
                ->with('error', 'Please create owner account first');
        }

        $ownerEmail = session('owner_email');

        // Clear installation session data
        session()->forget(['db_mode', 'db_configured', 'owner_created', 'owner_email']);

        return view('install.finish', compact('ownerEmail'));
    }

    /**
     * Check server requirements
     */
    private function checkRequirements(): array
    {
        $requirements = [
            'php_version' => [
                'name' => 'PHP Version >= 8.2',
                'required' => true,
                'status' => version_compare(PHP_VERSION, '8.2.0', '>='),
                'current' => PHP_VERSION,
            ],
            'extensions' => [
                'openssl' => [
                    'name' => 'OpenSSL Extension',
                    'required' => true,
                    'status' => extension_loaded('openssl'),
                ],
                'pdo' => [
                    'name' => 'PDO Extension',
                    'required' => true,
                    'status' => extension_loaded('pdo'),
                ],
                'pdo_mysql' => [
                    'name' => 'PDO MySQL Extension',
                    'required' => true,
                    'status' => extension_loaded('pdo_mysql'),
                ],
                'mbstring' => [
                    'name' => 'Mbstring Extension',
                    'required' => true,
                    'status' => extension_loaded('mbstring'),
                ],
                'tokenizer' => [
                    'name' => 'Tokenizer Extension',
                    'required' => true,
                    'status' => extension_loaded('tokenizer'),
                ],
                'xml' => [
                    'name' => 'XML Extension',
                    'required' => true,
                    'status' => extension_loaded('xml'),
                ],
                'ctype' => [
                    'name' => 'Ctype Extension',
                    'required' => true,
                    'status' => extension_loaded('ctype'),
                ],
                'json' => [
                    'name' => 'JSON Extension',
                    'required' => true,
                    'status' => extension_loaded('json'),
                ],
                'bcmath' => [
                    'name' => 'BCMath Extension',
                    'required' => true,
                    'status' => extension_loaded('bcmath'),
                ],
                'curl' => [
                    'name' => 'cURL Extension',
                    'required' => true,
                    'status' => extension_loaded('curl'),
                ],
                'fileinfo' => [
                    'name' => 'Fileinfo Extension',
                    'required' => true,
                    'status' => extension_loaded('fileinfo'),
                ],
                'gd' => [
                    'name' => 'GD Extension',
                    'required' => false,
                    'status' => extension_loaded('gd'),
                ],
                'zip' => [
                    'name' => 'ZIP Extension',
                    'required' => false,
                    'status' => extension_loaded('zip'),
                ],
            ],
            'permissions' => [
                'storage' => [
                    'name' => 'storage/',
                    'required' => true,
                    'status' => is_writable(storage_path()),
                    'path' => storage_path(),
                ],
                'bootstrap_cache' => [
                    'name' => 'bootstrap/cache/',
                    'required' => true,
                    'status' => is_writable(base_path('bootstrap/cache')),
                    'path' => base_path('bootstrap/cache'),
                ],
                'env' => [
                    'name' => '.env file',
                    'required' => true,
                    'status' => File::exists(base_path('.env')) ? is_writable(base_path('.env')) : is_writable(base_path()),
                    'path' => base_path('.env'),
                ],
            ],
        ];

        // Calculate overall status
        $allPassed = true;

        if (!$requirements['php_version']['status']) {
            $allPassed = false;
        }

        foreach ($requirements['extensions'] as $ext) {
            if ($ext['required'] && !$ext['status']) {
                $allPassed = false;
                break;
            }
        }

        foreach ($requirements['permissions'] as $perm) {
            if ($perm['required'] && !$perm['status']) {
                $allPassed = false;
                break;
            }
        }

        $requirements['all_passed'] = $allPassed;

        return $requirements;
    }
}
