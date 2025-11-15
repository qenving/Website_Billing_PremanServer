<?php

namespace App\Events;

use App\Models\Invoice;
use App\Models\Payment;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class InvoicePaidEvent
{
    use Dispatchable, SerializesModels;

    public Invoice $invoice;
    public Payment $payment;

    public function __construct(Invoice $invoice, Payment $payment)
    {
        $this->invoice = $invoice;
        $this->payment = $payment;
    }
}
