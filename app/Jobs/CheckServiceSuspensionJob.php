<?php

namespace App\Jobs;

use App\Models\Service;
use App\Models\Invoice;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CheckServiceSuspensionJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Get active services with overdue invoices
        $services = Service::where('status', 'active')
            ->whereHas('invoices', function ($query) {
                $query->where('status', 'unpaid')
                    ->where('due_date', '<', Carbon::now()->subDays(config('billing.suspension_grace_days', 7)));
            })
            ->get();

        foreach ($services as $service) {
            try {
                // Check if service has overdue unpaid invoices
                $overdueInvoices = $service->invoices()
                    ->where('status', 'unpaid')
                    ->where('due_date', '<', Carbon::now()->subDays(config('billing.suspension_grace_days', 7)))
                    ->count();

                if ($overdueInvoices > 0) {
                    // Suspend the service
                    $this->suspendService($service);

                    \Log::info('Service ' . $service->id . ' suspended due to ' . $overdueInvoices . ' overdue invoice(s)');
                }
            } catch (\Exception $e) {
                \Log::error('Failed to check suspension for service ' . $service->id . ': ' . $e->getMessage());
            }
        }

        // Check for services to terminate (suspended for too long)
        $this->checkTermination();
    }

    /**
     * Suspend a service
     */
    private function suspendService(Service $service): void
    {
        // Get the provisioning module if exists
        $product = $service->product;

        if ($product->module) {
            $moduleClass = "App\\Extensions\\Provisioning\\" . ucfirst($product->module) . "Module";

            if (class_exists($moduleClass)) {
                try {
                    $provisioner = new $moduleClass($product->module_config);

                    $result = $provisioner->suspendAccount([
                        'service_id' => $service->id,
                        'username' => $service->username,
                        'reason' => 'Overdue payment',
                    ]);

                    if (!$result['success']) {
                        \Log::error('Failed to suspend service on remote server: ' . ($result['error'] ?? 'Unknown error'));
                    }
                } catch (\Exception $e) {
                    \Log::error('Exception while suspending service on remote server: ' . $e->getMessage());
                }
            }
        }

        // Update service status
        $service->update([
            'status' => 'suspended',
            'suspended_at' => Carbon::now(),
            'suspension_reason' => 'Overdue payment',
        ]);

        // Send suspension notification email (optional)
        // Mail::to($service->client->user->email)->send(new ServiceSuspendedMail($service));
    }

    /**
     * Check for services to terminate
     */
    private function checkTermination(): void
    {
        $services = Service::where('status', 'suspended')
            ->where('suspended_at', '<', Carbon::now()->subDays(config('billing.termination_days', 30)))
            ->get();

        foreach ($services as $service) {
            try {
                $this->terminateService($service);
                \Log::info('Service ' . $service->id . ' terminated after extended suspension');
            } catch (\Exception $e) {
                \Log::error('Failed to terminate service ' . $service->id . ': ' . $e->getMessage());
            }
        }
    }

    /**
     * Terminate a service
     */
    private function terminateService(Service $service): void
    {
        // Get the provisioning module if exists
        $product = $service->product;

        if ($product->module) {
            $moduleClass = "App\\Extensions\\Provisioning\\" . ucfirst($product->module) . "Module";

            if (class_exists($moduleClass)) {
                try {
                    $provisioner = new $moduleClass($product->module_config);

                    $result = $provisioner->terminateAccount([
                        'service_id' => $service->id,
                        'username' => $service->username,
                        'reason' => 'Extended non-payment',
                    ]);

                    if (!$result['success']) {
                        \Log::error('Failed to terminate service on remote server: ' . ($result['error'] ?? 'Unknown error'));
                    }
                } catch (\Exception $e) {
                    \Log::error('Exception while terminating service on remote server: ' . $e->getMessage());
                }
            }
        }

        // Update service status
        $service->update([
            'status' => 'terminated',
            'terminated_at' => Carbon::now(),
            'termination_reason' => 'Extended non-payment',
        ]);

        // Send termination notification email (optional)
        // Mail::to($service->client->user->email)->send(new ServiceTerminatedMail($service));
    }
}
