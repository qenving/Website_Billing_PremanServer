@extends('layouts.app-client')

@section('title', 'Invoice Details')

@section('content')
<div class="container mx-auto px-4 py-8 max-w-4xl">
    <div class="mb-6">
        <a href="{{ route('client.invoices.index') }}" class="text-blue-600 hover:text-blue-800 text-sm font-medium">← Back to Invoices</a>
        <h1 class="text-3xl font-bold text-gray-900 mt-2">Invoice #{{ $invoice->invoice_number }}</h1>
    </div>

    @if($invoice->status == 'unpaid' && session('success'))
    <div class="mb-6 bg-green-50 border-l-4 border-green-500 p-4 rounded">
        <p class="text-green-700">{{ session('success') }}</p>
    </div>
    @endif

    <div class="bg-white rounded-lg shadow p-8 mb-6">
        <div class="flex justify-between items-start mb-8">
            <div>
                <h2 class="text-2xl font-bold">{{ config('app.name') }}</h2>
                <p class="text-gray-600 mt-2">Invoice #{{ $invoice->invoice_number }}</p>
            </div>
            <div class="text-right">
                @if($invoice->status == 'paid')
                <span class="px-4 py-2 bg-green-100 text-green-800 rounded-full font-semibold">PAID</span>
                @else
                <span class="px-4 py-2 bg-red-100 text-red-800 rounded-full font-semibold">UNPAID</span>
                @endif
            </div>
        </div>

        <div class="grid grid-cols-2 gap-8 mb-8 pb-8 border-b">
            <div>
                <h3 class="text-sm font-semibold text-gray-600 uppercase mb-2">Bill To</h3>
                <p class="font-medium">{{ auth()->user()->name }}</p>
                <p class="text-gray-600">{{ auth()->user()->email }}</p>
            </div>
            <div class="text-right">
                <div class="mb-2">
                    <span class="text-sm text-gray-600">Date:</span>
                    <span class="font-medium">{{ $invoice->created_at->format('M d, Y') }}</span>
                </div>
                <div>
                    <span class="text-sm text-gray-600">Due:</span>
                    <span class="font-medium">{{ $invoice->due_date ? $invoice->due_date->format('M d, Y') : 'N/A' }}</span>
                </div>
            </div>
        </div>

        <table class="w-full mb-8">
            <thead>
                <tr class="border-b">
                    <th class="px-4 py-3 text-left text-sm font-semibold">Description</th>
                    <th class="px-4 py-3 text-right text-sm font-semibold">Qty</th>
                    <th class="px-4 py-3 text-right text-sm font-semibold">Price</th>
                    <th class="px-4 py-3 text-right text-sm font-semibold">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($invoice->items as $item)
                <tr class="border-b">
                    <td class="px-4 py-4">{{ $item->description }}</td>
                    <td class="px-4 py-4 text-right">{{ $item->quantity }}</td>
                    <td class="px-4 py-4 text-right">${{ number_format($item->unit_price, 2) }}</td>
                    <td class="px-4 py-4 text-right font-medium">${{ number_format($item->total, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr class="border-b">
                    <td colspan="3" class="px-4 py-3 text-right font-medium">Subtotal:</td>
                    <td class="px-4 py-3 text-right font-medium">${{ number_format($invoice->subtotal, 2) }}</td>
                </tr>
                @if($invoice->tax > 0)
                <tr class="border-b">
                    <td colspan="3" class="px-4 py-3 text-right font-medium">Tax:</td>
                    <td class="px-4 py-3 text-right font-medium">${{ number_format($invoice->tax, 2) }}</td>
                </tr>
                @endif
                <tr>
                    <td colspan="3" class="px-4 py-4 text-right text-lg font-bold">Total:</td>
                    <td class="px-4 py-4 text-right text-lg font-bold text-blue-600">${{ number_format($invoice->total, 2) }}</td>
                </tr>
            </tfoot>
        </table>

        @if($invoice->status == 'unpaid')
        <div class="mt-8 pt-8 border-t">
            <h3 class="text-lg font-semibold mb-4">Payment Options</h3>
            <form method="POST" action="{{ route('client.invoices.pay', $invoice) }}">
                @csrf
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium mb-2">Select Payment Gateway</label>
                        <select name="gateway" required class="w-full px-4 py-2 border rounded-lg">
                            <option value="">Choose payment method...</option>
                            @foreach($paymentGateways ?? [] as $gateway)
                            <option value="{{ $gateway->slug }}">{{ $gateway->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white px-8 py-3 rounded-lg font-semibold text-lg">
                        Pay ${{ number_format($invoice->total, 2) }} Now
                    </button>
                </div>
            </form>
        </div>
        @elseif($invoice->payments->count() > 0)
        <div class="mt-8 pt-8 border-t">
            <h3 class="text-lg font-semibold mb-4 text-green-600">✓ Payment Received</h3>
            <p class="text-sm text-gray-600">Paid on {{ $invoice->payments->first()->created_at->format('M d, Y H:i') }} via {{ ucfirst($invoice->payments->first()->gateway) }}</p>
        </div>
        @endif
    </div>
</div>
@endsection
