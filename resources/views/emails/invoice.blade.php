<x-mail::message>
# Invoice #{{ $invoice->invoice_number }}

Hello {{ $client->user->name }},

Thank you for your business. Please find your invoice details below:

<x-mail::panel>
**Invoice Number:** #{{ $invoice->invoice_number }}
**Invoice Date:** {{ $invoice->invoice_date->format('F d, Y') }}
**Due Date:** {{ $invoice->due_date->format('F d, Y') }}
**Status:** {{ ucfirst($invoice->status) }}
</x-mail::panel>

## Invoice Items

| Description | Quantity | Unit Price | Total |
|------------|----------|------------|-------|
@foreach($items as $item)
| {{ $item->description }} | {{ $item->quantity }} | ${{ number_format($item->unit_price, 2) }} | ${{ number_format($item->quantity * $item->unit_price, 2) }} |
@endforeach

---

**Subtotal:** ${{ number_format($invoice->subtotal, 2) }}
@if($invoice->tax > 0)
**Tax:** ${{ number_format($invoice->tax, 2) }}
@endif
@if($invoice->discount > 0)
**Discount:** -${{ number_format($invoice->discount, 2) }}
@endif
**Total:** ${{ number_format($invoice->total, 2) }}

@if($invoice->status != 'paid')
<x-mail::button :url="route('client.invoices.show', $invoice)">
View & Pay Invoice
</x-mail::button>
@else
<x-mail::button :url="route('client.invoices.show', $invoice)">
View Invoice
</x-mail::button>
@endif

If you have any questions about this invoice, please contact our support team.

Thanks,
{{ config('app.name') }}
</x-mail::message>
