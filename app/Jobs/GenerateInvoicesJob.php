<?php

namespace App\Jobs;

use App\Models\Service;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Mail\InvoiceMail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;

class GenerateInvoicesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Get services that need invoicing (next_due_date is within 7 days and invoice not generated)
        $services = Service::where('status', 'active')
            ->where('billing_cycle', '!=', 'one_time')
            ->where('next_due_date', '<=', Carbon::now()->addDays(7))
            ->whereDoesntHave('invoices', function ($query) {
                $query->where('invoice_date', '>=', Carbon::now()->subDays(7))
                    ->where('status', '!=', 'cancelled');
            })
            ->get();

        foreach ($services as $service) {
            try {
                DB::beginTransaction();

                // Calculate invoice amount
                $amount = $service->price;

                // Create invoice
                $invoice = Invoice::create([
                    'client_id' => $service->client_id,
                    'service_id' => $service->id,
                    'invoice_number' => $this->generateInvoiceNumber(),
                    'invoice_date' => Carbon::now(),
                    'due_date' => $service->next_due_date,
                    'subtotal' => $amount,
                    'tax' => 0, // Calculate tax based on client location if needed
                    'total' => $amount,
                    'status' => 'unpaid',
                    'notes' => 'Recurring invoice for ' . $service->product->name,
                ]);

                // Create invoice item
                InvoiceItem::create([
                    'invoice_id' => $invoice->id,
                    'description' => $service->product->name . ' - ' . ucfirst($service->billing_cycle) . ' Billing',
                    'quantity' => 1,
                    'unit_price' => $amount,
                ]);

                // Update service next due date
                $service->next_due_date = $this->calculateNextDueDate($service->next_due_date, $service->billing_cycle);
                $service->save();

                DB::commit();

                // Send invoice email
                try {
                    Mail::to($service->client->user->email)
                        ->send(new InvoiceMail($invoice));
                } catch (\Exception $e) {
                    \Log::error('Failed to send invoice email: ' . $e->getMessage());
                }

            } catch (\Exception $e) {
                DB::rollBack();
                \Log::error('Failed to generate invoice for service ' . $service->id . ': ' . $e->getMessage());
            }
        }
    }

    /**
     * Generate unique invoice number
     */
    private function generateInvoiceNumber(): string
    {
        $prefix = 'INV';
        $year = date('Y');
        $month = date('m');

        $lastInvoice = Invoice::where('invoice_number', 'like', $prefix . $year . $month . '%')
            ->orderBy('invoice_number', 'desc')
            ->first();

        if ($lastInvoice) {
            $lastNumber = (int) substr($lastInvoice->invoice_number, -4);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return $prefix . $year . $month . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Calculate next due date based on billing cycle
     */
    private function calculateNextDueDate(Carbon $currentDueDate, string $billingCycle): Carbon
    {
        return match($billingCycle) {
            'monthly' => $currentDueDate->copy()->addMonth(),
            'quarterly' => $currentDueDate->copy()->addMonths(3),
            'semi_annually' => $currentDueDate->copy()->addMonths(6),
            'annually' => $currentDueDate->copy()->addYear(),
            'biennially' => $currentDueDate->copy()->addYears(2),
            'triennially' => $currentDueDate->copy()->addYears(3),
            default => $currentDueDate->copy()->addMonth(),
        };
    }
}
