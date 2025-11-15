<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Service;
use App\Services\ExtensionManager;
use Illuminate\Http\Request;

class ServiceController extends Controller
{
    protected ExtensionManager $extensionManager;

    public function __construct(ExtensionManager $extensionManager)
    {
        $this->extensionManager = $extensionManager;
    }

    public function index(Request $request)
    {
        $query = Service::with(['client.user', 'product']);

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by product
        if ($request->filled('product')) {
            $query->where('product_id', $request->product);
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('client.user', function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $services = $query->latest()->paginate(20);

        return view('admin.services.index', compact('services'));
    }

    public function show(Service $service)
    {
        $service->load(['client.user', 'product', 'invoices']);

        // Get service status from provisioning provider if available
        $status = null;
        if ($service->provisioning_data && $service->product->provisioning_extension) {
            try {
                $provider = $this->extensionManager->getProvisioningProvider(
                    $service->product->provisioning_extension
                );

                if ($provider) {
                    $status = $provider->getServiceStatus($service);
                }
            } catch (\Exception $e) {
                // Log error but don't break the page
                \Log::error('Failed to get service status', [
                    'service_id' => $service->id,
                    'error' => $e->getMessage()
                ]);
            }
        }

        return view('admin.services.show', compact('service', 'status'));
    }

    public function provision(Service $service)
    {
        if ($service->status !== 'pending') {
            return back()->with('error', 'Service must be in pending status to provision.');
        }

        if (!$service->product->provisioning_extension) {
            return back()->with('error', 'Product has no provisioning extension configured.');
        }

        try {
            $provider = $this->extensionManager->getProvisioningProvider(
                $service->product->provisioning_extension
            );

            if (!$provider) {
                return back()->with('error', 'Provisioning provider not found or not enabled.');
            }

            $config = json_decode($service->product->provisioning_config, true) ?? [];
            $result = $provider->createService($service, $config, $service->client);

            if ($result->success) {
                $service->update([
                    'status' => 'active',
                    'provisioning_data' => $result->metadata,
                    'provisioned_at' => now(),
                ]);

                // Audit log
                AuditLog::create([
                    'user_id' => auth()->id(),
                    'action' => 'service.provisioned',
                    'description' => "Provisioned service #{$service->id} for client {$service->client->user->email}",
                    'ip_address' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                ]);

                return back()->with('success', 'Service provisioned successfully.');
            } else {
                return back()->with('error', 'Provisioning failed: ' . $result->errorMessage);
            }

        } catch (\Exception $e) {
            \Log::error('Service provisioning error', [
                'service_id' => $service->id,
                'error' => $e->getMessage()
            ]);

            return back()->with('error', 'Provisioning failed: ' . $e->getMessage());
        }
    }

    public function suspend(Service $service)
    {
        if ($service->status !== 'active') {
            return back()->with('error', 'Only active services can be suspended.');
        }

        try {
            if ($service->product->provisioning_extension) {
                $provider = $this->extensionManager->getProvisioningProvider(
                    $service->product->provisioning_extension
                );

                if ($provider) {
                    $provider->suspendService($service);
                }
            }

            $service->update([
                'status' => 'suspended',
                'suspended_at' => now(),
            ]);

            // Audit log
            AuditLog::create([
                'user_id' => auth()->id(),
                'action' => 'service.suspended',
                'description' => "Suspended service #{$service->id} for client {$service->client->user->email}",
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);

            return back()->with('success', 'Service suspended successfully.');

        } catch (\Exception $e) {
            \Log::error('Service suspend error', [
                'service_id' => $service->id,
                'error' => $e->getMessage()
            ]);

            return back()->with('error', 'Suspend failed: ' . $e->getMessage());
        }
    }

    public function unsuspend(Service $service)
    {
        if ($service->status !== 'suspended') {
            return back()->with('error', 'Only suspended services can be unsuspended.');
        }

        try {
            if ($service->product->provisioning_extension) {
                $provider = $this->extensionManager->getProvisioningProvider(
                    $service->product->provisioning_extension
                );

                if ($provider) {
                    $provider->unsuspendService($service);
                }
            }

            $service->update([
                'status' => 'active',
                'suspended_at' => null,
            ]);

            // Audit log
            AuditLog::create([
                'user_id' => auth()->id(),
                'action' => 'service.unsuspended',
                'description' => "Unsuspended service #{$service->id} for client {$service->client->user->email}",
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);

            return back()->with('success', 'Service unsuspended successfully.');

        } catch (\Exception $e) {
            \Log::error('Service unsuspend error', [
                'service_id' => $service->id,
                'error' => $e->getMessage()
            ]);

            return back()->with('error', 'Unsuspend failed: ' . $e->getMessage());
        }
    }

    public function terminate(Service $service)
    {
        if ($service->status === 'terminated') {
            return back()->with('error', 'Service is already terminated.');
        }

        try {
            if ($service->product->provisioning_extension) {
                $provider = $this->extensionManager->getProvisioningProvider(
                    $service->product->provisioning_extension
                );

                if ($provider) {
                    $provider->terminateService($service);
                }
            }

            $service->update([
                'status' => 'terminated',
                'terminated_at' => now(),
            ]);

            // Audit log
            AuditLog::create([
                'user_id' => auth()->id(),
                'action' => 'service.terminated',
                'description' => "Terminated service #{$service->id} for client {$service->client->user->email}",
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);

            return back()->with('success', 'Service terminated successfully.');

        } catch (\Exception $e) {
            \Log::error('Service terminate error', [
                'service_id' => $service->id,
                'error' => $e->getMessage()
            ]);

            return back()->with('error', 'Termination failed: ' . $e->getMessage());
        }
    }
}
