<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Extension;
use App\Models\ExtensionConfig;
use App\Services\ExtensionManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ExtensionController extends Controller
{
    protected ExtensionManager $extensionManager;

    public function __construct(ExtensionManager $extensionManager)
    {
        $this->extensionManager = $extensionManager;
    }

    public function index(Request $request)
    {
        $type = $request->get('type', 'all');

        $query = Extension::query();

        if ($type !== 'all') {
            $query->where('type', $type);
        }

        $extensions = $query->with('configs')->get();

        return view('admin.extensions.index', compact('extensions', 'type'));
    }

    public function toggle(Extension $extension)
    {
        // Validate config before enabling
        if (!$extension->enabled) {
            $configs = $extension->configs->pluck('value', 'key')->toArray();

            if ($extension->type === 'payment_gateway') {
                $gateway = $this->extensionManager->getPaymentGateway($extension->name);
                if (!$gateway || !$gateway->validateConfig($configs)) {
                    return back()->with('error', 'Cannot enable extension. Please configure it first.');
                }
            } elseif ($extension->type === 'provisioning_panel') {
                $provider = $this->extensionManager->getProvisioningProvider($extension->name);
                if (!$provider || !$provider->validateConfig($configs)) {
                    return back()->with('error', 'Cannot enable extension. Please configure it first.');
                }
            }
        }

        $extension->update(['enabled' => !$extension->enabled]);

        $status = $extension->enabled ? 'enabled' : 'disabled';

        // Audit log
        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => "extension.{$status}",
            'description' => "Extension {$status}: {$extension->display_name}",
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        return back()->with('success', "Extension {$status} successfully.");
    }

    public function configure(Extension $extension)
    {
        // Get config schema from extension class
        $schema = [];

        if ($extension->type === 'payment_gateway') {
            try {
                $gateway = new $extension->class_name([]);
                $schema = $gateway->getConfigSchema();
            } catch (\Exception $e) {
                return back()->with('error', 'Failed to load extension configuration schema.');
            }
        } elseif ($extension->type === 'provisioning_panel') {
            try {
                $provider = new $extension->class_name([]);
                $schema = $provider->getConfigSchema();
            } catch (\Exception $e) {
                return back()->with('error', 'Failed to load extension configuration schema.');
            }
        }

        // Get current config values
        $currentConfig = $extension->configs->pluck('value', 'key')->toArray();

        return view('admin.extensions.configure', compact('extension', 'schema', 'currentConfig'));
    }

    public function saveConfig(Request $request, Extension $extension)
    {
        // Validate based on schema
        $configData = $request->except(['_token', '_method']);

        DB::beginTransaction();

        try {
            // Delete old configs
            $extension->configs()->delete();

            // Save new configs
            foreach ($configData as $key => $value) {
                ExtensionConfig::create([
                    'extension_id' => $extension->id,
                    'key' => $key,
                    'value' => is_array($value) ? json_encode($value) : $value,
                ]);
            }

            DB::commit();

            // Audit log
            AuditLog::create([
                'user_id' => auth()->id(),
                'action' => 'extension.configured',
                'description' => "Configured extension: {$extension->display_name}",
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            return redirect()->route('admin.extensions.index')
                ->with('success', 'Extension configured successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Failed to save configuration: ' . $e->getMessage());
        }
    }

    public function healthCheck(Extension $extension)
    {
        try {
            $result = null;

            if ($extension->type === 'payment_gateway') {
                $gateway = $this->extensionManager->getPaymentGateway($extension->name);
                if ($gateway) {
                    $result = $gateway->healthCheck();
                }
            } elseif ($extension->type === 'provisioning_panel') {
                $provider = $this->extensionManager->getProvisioningProvider($extension->name);
                if ($provider) {
                    $result = $provider->healthCheck();
                }
            }

            if (!$result) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Extension not found or not enabled',
                ], 404);
            }

            return response()->json([
                'status' => $result->status,
                'message' => $result->message,
                'checked_at' => $result->checkedAt->toDateTimeString(),
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
