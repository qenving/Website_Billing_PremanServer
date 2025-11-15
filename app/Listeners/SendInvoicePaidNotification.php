<?php

namespace App\Listeners;

use App\Events\InvoicePaidEvent;
use App\Mail\PaymentReceivedMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendInvoicePaidNotification implements ShouldQueue
{
    public function handle(InvoicePaidEvent $event): void
    {
        // Send payment confirmation email
        Mail::to($event->invoice->client->user->email)
            ->send(new PaymentReceivedMail($event->payment));

        // Log activity
        \App\Models\ActivityLog::log(
            'payment_received',
            'Payment received for invoice #' . $event->invoice->invoice_number,
            $event->payment,
            ['amount' => $event->payment->amount]
        );
    }
}
