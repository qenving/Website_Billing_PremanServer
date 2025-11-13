<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Extension;
use App\Services\ExtensionManager;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class HealthCheckController extends Controller
{
    protected ExtensionManager $extensionManager;

    public function __construct(ExtensionManager $extensionManager)
    {
        $this->extensionManager = $extensionManager;
    }

    public function index()
    {
        $checks = [
            'database' => $this->checkDatabase(),
            'cache' => $this->checkCache(),
            'storage' => $this->checkStorage(),
            'queue' => $this->checkQueue(),
        ];

        // Extension health checks
        $extensions = Extension::where('enabled', true)->get();
        $extensionHealth = [];

        foreach ($extensions as $extension) {
            try {
                if ($extension->type === 'payment_gateway') {
                    $gateway = $this->extensionManager->getPaymentGateway($extension->name);
                    if ($gateway) {
                        $extensionHealth[$extension->name] = $gateway->healthCheck();
                    }
                } elseif ($extension->type === 'provisioning_panel') {
                    $provider = $this->extensionManager->getProvisioningProvider($extension->name);
                    if ($provider) {
                        $extensionHealth[$extension->name] = $provider->healthCheck();
                    }
                }
            } catch (\Exception $e) {
                $extensionHealth[$extension->name] = (object) [
                    'serviceName' => $extension->display_name,
                    'status' => 'error',
                    'message' => $e->getMessage(),
                    'checkedAt' => now(),
                ];
            }
        }

        return view('admin.health.index', compact('checks', 'extensionHealth'));
    }

    public function checkAll()
    {
        // Run all checks and return JSON
        $checks = [
            'database' => $this->checkDatabase(),
            'cache' => $this->checkCache(),
            'storage' => $this->checkStorage(),
            'queue' => $this->checkQueue(),
        ];

        $allOk = collect($checks)->every(fn($check) => $check['status'] === 'ok');

        return response()->json([
            'status' => $allOk ? 'ok' : 'error',
            'checks' => $checks,
            'checked_at' => now()->toDateTimeString(),
        ]);
    }

    protected function checkDatabase(): array
    {
        try {
            DB::connection()->getPdo();
            $version = DB::select('SELECT VERSION() as version')[0]->version;

            return [
                'status' => 'ok',
                'message' => "Connected to database (MySQL {$version})",
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Database connection failed: ' . $e->getMessage(),
            ];
        }
    }

    protected function checkCache(): array
    {
        try {
            $testKey = 'health_check_' . time();
            $testValue = 'test_value';

            Cache::put($testKey, $testValue, 60);
            $retrieved = Cache::get($testKey);
            Cache::forget($testKey);

            if ($retrieved === $testValue) {
                return [
                    'status' => 'ok',
                    'message' => 'Cache is working (driver: ' . config('cache.default') . ')',
                ];
            } else {
                return [
                    'status' => 'warning',
                    'message' => 'Cache read/write mismatch',
                ];
            }
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Cache error: ' . $e->getMessage(),
            ];
        }
    }

    protected function checkStorage(): array
    {
        try {
            $storagePath = storage_path();
            $writable = is_writable($storagePath);

            if ($writable) {
                $freeSpace = disk_free_space($storagePath);
                $totalSpace = disk_total_space($storagePath);
                $usedPercent = round((($totalSpace - $freeSpace) / $totalSpace) * 100, 2);

                $status = $usedPercent > 90 ? 'warning' : 'ok';
                $message = "Storage writable. Used: {$usedPercent}%";

                return [
                    'status' => $status,
                    'message' => $message,
                ];
            } else {
                return [
                    'status' => 'error',
                    'message' => 'Storage directory is not writable',
                ];
            }
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Storage check error: ' . $e->getMessage(),
            ];
        }
    }

    protected function checkQueue(): array
    {
        try {
            $driver = config('queue.default');

            // Check if queue table exists (for database driver)
            if ($driver === 'database') {
                $jobsCount = DB::table('jobs')->count();
                $failedCount = DB::table('failed_jobs')->count();

                $status = $failedCount > 10 ? 'warning' : 'ok';
                $message = "Queue driver: {$driver}. Pending: {$jobsCount}, Failed: {$failedCount}";

                return [
                    'status' => $status,
                    'message' => $message,
                ];
            } else {
                return [
                    'status' => 'ok',
                    'message' => "Queue driver: {$driver}",
                ];
            }
        } catch (\Exception $e) {
            return [
                'status' => 'warning',
                'message' => 'Queue check warning: ' . $e->getMessage(),
            ];
        }
    }
}
