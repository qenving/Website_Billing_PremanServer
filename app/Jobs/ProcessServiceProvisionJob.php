<?php

namespace App\Jobs;

use App\Models\Service;
use App\Mail\ServiceCreatedMail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class ProcessServiceProvisionJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public Service $service;

    /**
     * Create a new job instance.
     */
    public function __construct(Service $service)
    {
        $this->service = $service;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Check if service is already provisioned
        if ($this->service->status !== 'pending') {
            \Log::info('Service ' . $this->service->id . ' is not pending, skipping provision');
            return;
        }

        try {
            $product = $this->service->product;
            $module = $product->module;

            // Check if product has auto-provision enabled
            if (!$product->auto_provision) {
                \Log::info('Auto-provision disabled for product ' . $product->id);
                return;
            }

            // Get the provisioning module
            $moduleClass = "App\\Extensions\\Provisioning\\" . ucfirst($module) . "Module";

            if (!class_exists($moduleClass)) {
                \Log::error('Provisioning module not found: ' . $moduleClass);
                $this->service->update([
                    'status' => 'pending',
                    'provision_error' => 'Provisioning module not available'
                ]);
                return;
            }

            // Initialize the module
            $provisioner = new $moduleClass($product->module_config);

            // Prepare provisioning parameters
            $params = [
                'service_id' => $this->service->id,
                'domain' => $this->service->domain,
                'username' => $this->service->username ?? $this->generateUsername(),
                'password' => $this->service->password ?? $this->generatePassword(),
                'email' => $this->service->client->user->email,
                'package' => $product->module_package,
                'config_options' => $this->service->config_options,
            ];

            // Call provisioning module
            $result = $provisioner->createAccount($params);

            if ($result['success']) {
                // Update service with provisioning details
                $this->service->update([
                    'status' => 'active',
                    'username' => $params['username'],
                    'password' => encrypt($params['password']), // Encrypt password
                    'server_details' => json_encode($result['data'] ?? []),
                    'provisioned_at' => now(),
                ]);

                // Send service created email
                try {
                    Mail::to($this->service->client->user->email)
                        ->send(new ServiceCreatedMail($this->service));
                } catch (\Exception $e) {
                    \Log::error('Failed to send service created email: ' . $e->getMessage());
                }

                \Log::info('Service ' . $this->service->id . ' provisioned successfully');
            } else {
                // Provisioning failed
                $this->service->update([
                    'status' => 'pending',
                    'provision_error' => $result['error'] ?? 'Unknown provisioning error'
                ]);

                \Log::error('Failed to provision service ' . $this->service->id . ': ' . ($result['error'] ?? 'Unknown error'));
            }

        } catch (\Exception $e) {
            \Log::error('Exception during service provision: ' . $e->getMessage());

            $this->service->update([
                'status' => 'pending',
                'provision_error' => 'System error: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Generate random username
     */
    private function generateUsername(): string
    {
        return 'user_' . strtolower(\Str::random(8));
    }

    /**
     * Generate secure random password
     */
    private function generatePassword(): string
    {
        $uppercase = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $lowercase = 'abcdefghijklmnopqrstuvwxyz';
        $numbers = '0123456789';
        $special = '!@#$%^&*';

        $password = '';
        $password .= $uppercase[random_int(0, strlen($uppercase) - 1)];
        $password .= $lowercase[random_int(0, strlen($lowercase) - 1)];
        $password .= $numbers[random_int(0, strlen($numbers) - 1)];
        $password .= $special[random_int(0, strlen($special) - 1)];

        $allChars = $uppercase . $lowercase . $numbers . $special;
        for ($i = 0; $i < 12; $i++) {
            $password .= $allChars[random_int(0, strlen($allChars) - 1)];
        }

        return str_shuffle($password);
    }
}
