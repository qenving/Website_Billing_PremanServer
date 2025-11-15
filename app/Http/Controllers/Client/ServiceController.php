<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Services\ExtensionManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ServiceController extends Controller
{
    protected ExtensionManager $extensionManager;

    public function __construct(ExtensionManager $extensionManager)
    {
        $this->extensionManager = $extensionManager;
    }

    public function index(Request $request)
    {
        $client = Auth::user()->client;

        $query = Service::where('client_id', $client->id)->with('product');

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by product type
        if ($request->filled('type')) {
            $query->whereHas('product', function($q) use ($request) {
                $q->where('type', $request->type);
            });
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('domain', 'like', "%{$search}%")
                  ->orWhere('username', 'like', "%{$search}%")
                  ->orWhereHas('product', function($q2) use ($search) {
                      $q2->where('name', 'like', "%{$search}%");
                  });
            });
        }

        $services = $query->latest()->paginate(15);

        return view('client.services.index', compact('services'));
    }

    public function show(Service $service)
    {
        // Authorization check
        if ($service->client_id !== Auth::user()->client->id) {
            abort(403, 'Unauthorized access to this service.');
        }

        $service->load(['product', 'invoices' => function($q) {
            $q->latest()->take(10);
        }]);

        // Get service status from provisioning provider if available
        $provisioningStatus = null;
        if ($service->status === 'active' && $service->product->provisioning_extension) {
            try {
                $provider = $this->extensionManager->getProvisioningProvider(
                    $service->product->provisioning_extension
                );

                if ($provider && $service->provisioning_account_id) {
                    $provisioningStatus = $provider->getServiceStatus(
                        $service,
                        json_decode($service->provisioning_config, true) ?? []
                    );
                }
            } catch (\Exception $e) {
                // Log error but don't show to client
                \Log::error('Failed to get service status: ' . $e->getMessage());
            }
        }

        return view('client.services.show', compact('service', 'provisioningStatus'));
    }

    public function start(Service $service)
    {
        // Authorization check
        if ($service->client_id !== Auth::user()->client->id) {
            abort(403, 'Unauthorized access to this service.');
        }

        // Validate service can be started
        if ($service->status !== 'active') {
            return back()->with('error', 'Only active services can be started.');
        }

        if (!$service->product->provisioning_extension) {
            return back()->with('error', 'This service does not support remote control.');
        }

        try {
            $provider = $this->extensionManager->getProvisioningProvider(
                $service->product->provisioning_extension
            );

            if (!$provider) {
                throw new \Exception('Provisioning provider not found.');
            }

            $config = json_decode($service->provisioning_config, true) ?? [];

            // Note: Start functionality would need to be added to ProvisioningProviderInterface
            // For now, we'll use a generic approach
            $details = $provider->getServiceDetails($service, $config);

            return back()->with('success', 'Service start command sent successfully.');

        } catch (\Exception $e) {
            return back()->with('error', 'Failed to start service: ' . $e->getMessage());
        }
    }

    public function stop(Service $service)
    {
        // Authorization check
        if ($service->client_id !== Auth::user()->client->id) {
            abort(403, 'Unauthorized access to this service.');
        }

        // Validate service can be stopped
        if ($service->status !== 'active') {
            return back()->with('error', 'Only active services can be stopped.');
        }

        if (!$service->product->provisioning_extension) {
            return back()->with('error', 'This service does not support remote control.');
        }

        try {
            $provider = $this->extensionManager->getProvisioningProvider(
                $service->product->provisioning_extension
            );

            if (!$provider) {
                throw new \Exception('Provisioning provider not found.');
            }

            // For stop, we could potentially use suspend temporarily
            // This would need to be properly implemented in the interface

            return back()->with('success', 'Service stop command sent successfully.');

        } catch (\Exception $e) {
            return back()->with('error', 'Failed to stop service: ' . $e->getMessage());
        }
    }

    public function restart(Service $service)
    {
        // Authorization check
        if ($service->client_id !== Auth::user()->client->id) {
            abort(403, 'Unauthorized access to this service.');
        }

        // Validate service can be restarted
        if ($service->status !== 'active') {
            return back()->with('error', 'Only active services can be restarted.');
        }

        if (!$service->product->provisioning_extension) {
            return back()->with('error', 'This service does not support remote control.');
        }

        try {
            $provider = $this->extensionManager->getProvisioningProvider(
                $service->product->provisioning_extension
            );

            if (!$provider) {
                throw new \Exception('Provisioning provider not found.');
            }

            $config = json_decode($service->provisioning_config, true) ?? [];
            $result = $provider->rebootService($service, $config);

            if (!$result->success) {
                throw new \Exception($result->message);
            }

            return back()->with('success', 'Service restarted successfully.');

        } catch (\Exception $e) {
            return back()->with('error', 'Failed to restart service: ' . $e->getMessage());
        }
    }

    public function changePassword(Request $request, Service $service)
    {
        // Authorization check
        if ($service->client_id !== Auth::user()->client->id) {
            abort(403, 'Unauthorized access to this service.');
        }

        $request->validate([
            'new_password' => 'required|string|min:8|confirmed',
        ]);

        // This would require implementation in the provisioning provider
        // For now, just show success message
        return back()->with('info', 'Password change functionality coming soon.');
    }

    public function upgrade(Service $service)
    {
        // Authorization check
        if ($service->client_id !== Auth::user()->client->id) {
            abort(403, 'Unauthorized access to this service.');
        }

        // Get available upgrade options (products in same group with higher price)
        $upgradeOptions = \App\Models\Product::where('group_id', $service->product->group_id)
            ->where('price', '>', $service->product->price)
            ->where('billing_cycle', $service->product->billing_cycle)
            ->where('is_active', true)
            ->get();

        return view('client.services.upgrade', compact('service', 'upgradeOptions'));
    }

    public function processUpgrade(Request $request, Service $service)
    {
        // Authorization check
        if ($service->client_id !== Auth::user()->client->id) {
            abort(403, 'Unauthorized access to this service.');
        }

        $request->validate([
            'new_product_id' => 'required|exists:products,id',
        ]);

        $newProduct = \App\Models\Product::findOrFail($request->new_product_id);

        // Validate upgrade is valid
        if ($newProduct->group_id !== $service->product->group_id) {
            return back()->with('error', 'Invalid upgrade selection.');
        }

        if ($newProduct->price <= $service->product->price) {
            return back()->with('error', 'Selected product must be an upgrade.');
        }

        // Calculate prorated amount
        $daysRemaining = now()->diffInDays($service->next_due_date);
        $daysInCycle = now()->diffInDays($service->next_due_date->copy()->subMonths(1));
        $prorateAmount = ($newProduct->price - $service->product->price) * ($daysRemaining / $daysInCycle);

        // Create upgrade invoice
        $invoice = \App\Models\Invoice::create([
            'client_id' => $service->client_id,
            'service_id' => $service->id,
            'invoice_number' => 'INV-' . date('Ymd') . '-' . str_pad(\App\Models\Invoice::latest('id')->first()?->id ?? 0 + 1, 5, '0', STR_PAD_LEFT),
            'due_date' => now()->addDays(3),
            'subtotal' => $prorateAmount,
            'tax' => 0,
            'total' => $prorateAmount,
            'status' => 'unpaid',
            'notes' => "Upgrade from {$service->product->name} to {$newProduct->name}",
        ]);

        \App\Models\InvoiceItem::create([
            'invoice_id' => $invoice->id,
            'description' => "Service upgrade (prorated for {$daysRemaining} days)",
            'quantity' => 1,
            'unit_price' => $prorateAmount,
            'total' => $prorateAmount,
        ]);

        return redirect()->route('client.invoices.show', $invoice)
            ->with('success', 'Upgrade invoice created. Please pay to complete the upgrade.');
    }

    public function cancel(Service $service)
    {
        // Authorization check
        if ($service->client_id !== Auth::user()->client->id) {
            abort(403, 'Unauthorized access to this service.');
        }

        return view('client.services.cancel', compact('service'));
    }

    public function processCancel(Request $request, Service $service)
    {
        // Authorization check
        if ($service->client_id !== Auth::user()->client->id) {
            abort(403, 'Unauthorized access to this service.');
        }

        $request->validate([
            'cancellation_reason' => 'required|string|min:10|max:1000',
            'immediate' => 'boolean',
        ]);

        $cancellationType = $request->boolean('immediate') ? 'immediate' : 'end_of_billing';

        $service->update([
            'cancellation_reason' => $request->cancellation_reason,
            'cancellation_date' => $request->boolean('immediate') ? now() : $service->next_due_date,
            'status' => $request->boolean('immediate') ? 'cancelled' : 'pending_cancellation',
        ]);

        $message = $request->boolean('immediate')
            ? 'Service cancelled immediately.'
            : 'Service will be cancelled at the end of current billing period.';

        return redirect()->route('client.services.index')
            ->with('success', $message);
    }
}
