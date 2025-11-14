<?php

namespace App\Jobs;

use App\Models\Payment;
use App\Models\Invoice;
use App\Mail\PaymentReceivedMail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class ProcessPaymentJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public Payment $payment;

    /**
     * Create a new job instance.
     */
    public function __construct(Payment $payment)
    {
        $this->payment = $payment;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Only process successful payments
        if ($this->payment->status !== 'success') {
            \Log::info('Payment ' . $this->payment->id . ' is not successful, skipping processing');
            return;
        }

        try {
            DB::beginTransaction();

            $invoice = $this->payment->invoice;

            // Calculate total paid amount for this invoice
            $totalPaid = $invoice->payments()
                ->where('status', 'success')
                ->sum('amount');

            // Update invoice status
            if ($totalPaid >= $invoice->total) {
                $invoice->update([
                    'status' => 'paid',
                    'paid_at' => now(),
                ]);

                \Log::info('Invoice ' . $invoice->id . ' marked as paid');

                // If invoice is for a service, activate/unsuspend it
                if ($invoice->service_id) {
                    $this->activateService($invoice->service);
                }
            } elseif ($totalPaid > 0) {
                $invoice->update(['status' => 'partial']);
                \Log::info('Invoice ' . $invoice->id . ' partially paid');
            }

            DB::commit();

            // Send payment confirmation email
            try {
                Mail::to($invoice->client->user->email)
                    ->send(new PaymentReceivedMail($this->payment));
            } catch (\Exception $e) {
                \Log::error('Failed to send payment confirmation email: ' . $e->getMessage());
            }

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Failed to process payment ' . $this->payment->id . ': ' . $e->getMessage());
        }
    }

    /**
     * Activate or unsuspend a service
     */
    private function activateService($service): void
    {
        if (!$service) {
            return;
        }

        try {
            $previousStatus = $service->status;

            // If service is pending, provision it
            if ($service->status === 'pending') {
                // Dispatch provisioning job
                ProcessServiceProvisionJob::dispatch($service);
                \Log::info('Dispatched provisioning for service ' . $service->id);
            }
            // If service is suspended, unsuspend it
            elseif ($service->status === 'suspended') {
                // Get the provisioning module if exists
                $product = $service->product;

                if ($product->module) {
                    $moduleClass = "App\\Extensions\\Provisioning\\" . ucfirst($product->module) . "Module";

                    if (class_exists($moduleClass)) {
                        $provisioner = new $moduleClass($product->module_config);

                        $result = $provisioner->unsuspendAccount([
                            'service_id' => $service->id,
                            'username' => $service->username,
                        ]);

                        if ($result['success']) {
                            $service->update([
                                'status' => 'active',
                                'suspended_at' => null,
                                'suspension_reason' => null,
                            ]);

                            \Log::info('Service ' . $service->id . ' unsuspended successfully');
                        } else {
                            \Log::error('Failed to unsuspend service on remote server: ' . ($result['error'] ?? 'Unknown error'));
                        }
                    }
                } else {
                    // No provisioning module, just update status
                    $service->update([
                        'status' => 'active',
                        'suspended_at' => null,
                        'suspension_reason' => null,
                    ]);

                    \Log::info('Service ' . $service->id . ' status updated to active');
                }
            }

        } catch (\Exception $e) {
            \Log::error('Failed to activate service ' . $service->id . ': ' . $e->getMessage());
        }
    }
}
