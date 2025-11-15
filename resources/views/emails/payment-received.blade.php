<x-mail::message>
# Payment Received - Thank You!

Hello {{ $client->user->name }},

We have successfully received your payment. Thank you for your business!

<x-mail::panel>
**Payment Amount:** ${{ number_format($payment->amount, 2) }}
**Transaction ID:** {{ $payment->transaction_id }}
**Payment Method:** {{ ucfirst($payment->gateway) }}
**Payment Date:** {{ $payment->created_at->format('F d, Y H:i:s') }}
**Status:** {{ ucfirst($payment->status) }}
</x-mail::panel>

## Invoice Details

**Invoice Number:** #{{ $invoice->invoice_number }}
**Invoice Date:** {{ $invoice->invoice_date->format('F d, Y') }}
**Invoice Total:** ${{ number_format($invoice->total, 2) }}
@if($invoice->status == 'paid')
**Invoice Status:** Paid in Full ✓
@else
**Remaining Balance:** ${{ number_format($invoice->total - $invoice->payments->sum('amount'), 2) }}
@endif

## What's Next?

@if($invoice->status == 'paid')
Your invoice has been paid in full. If this payment was for a new service, it will be activated shortly.

You can view your invoice and download a receipt from your client area.
@else
Thank you for your partial payment. The remaining balance is due by {{ $invoice->due_date->format('F d, Y') }}.
@endif

<x-mail::button :url="route('client.invoices.show', $invoice)">
View Invoice
</x-mail::button>

## Payment Receipt

You can download your payment receipt from your client area at any time.

- **Payment ID:** #{{ $payment->id }}
- **Amount Paid:** ${{ number_format($payment->amount, 2) }}
- **Payment Gateway:** {{ ucfirst($payment->gateway) }}

## Need Help?

If you have any questions about this payment or need assistance, please don't hesitate to contact our support team.

<x-mail::button :url="route('client.tickets.create')" color="success">
Contact Support
</x-mail::button>

We appreciate your business!

Thanks,
{{ config('app.name') }} Team
</x-mail::message>
