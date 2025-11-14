@extends('layouts.app-admin')

@section('title', 'Invoice Details')

@section('content')
<div class="container mx-auto px-4 py-6 max-w-4xl">
    <div class="mb-6 flex justify-between items-center">
        <div class="flex items-center">
            <a href="{{ route('admin.invoices.index') }}" class="text-gray-600 hover:text-gray-900 mr-4">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            </a>
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Invoice #{{ $invoice->invoice_number }}</h1>
            </div>
        </div>
        <div class="flex space-x-2">
            <a href="{{ route('admin.invoices.edit', $invoice) }}" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg font-semibold">Edit</a>
            @if($invoice->status == 'unpaid')
            <form method="POST" action="{{ route('admin.invoices.send', $invoice) }}" class="inline">
                @csrf
                <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded-lg font-semibold">Send Email</button>
            </form>
            @endif
        </div>
    </div>

    <div class="bg-white rounded-lg shadow-md p-8">
        <div class="flex justify-between items-start mb-8">
            <div>
                <h2 class="text-2xl font-bold text-gray-900">{{ config('app.name') }}</h2>
                <p class="text-gray-600 mt-2">Invoice #{{ $invoice->invoice_number }}</p>
            </div>
            <div class="text-right">
                @if($invoice->status == 'paid')
                <span class="px-4 py-2 bg-green-100 text-green-800 rounded-full text-sm font-semibold">PAID</span>
                @elseif($invoice->status == 'unpaid')
                <span class="px-4 py-2 bg-red-100 text-red-800 rounded-full text-sm font-semibold">UNPAID</span>
                @else
                <span class="px-4 py-2 bg-gray-100 text-gray-800 rounded-full text-sm font-semibold">{{ strtoupper($invoice->status) }}</span>
                @endif
            </div>
        </div>

        <div class="grid grid-cols-2 gap-8 mb-8 pb-8 border-b">
            <div>
                <h3 class="text-sm font-semibold text-gray-600 uppercase mb-2">Bill To</h3>
                <p class="font-medium text-gray-900">{{ $invoice->client->user->name ?? 'N/A' }}</p>
                <p class="text-gray-600">{{ $invoice->client->user->email ?? '' }}</p>
            </div>
            <div class="text-right">
                <div class="mb-2">
                    <span class="text-sm text-gray-600">Invoice Date:</span>
                    <span class="font-medium">{{ $invoice->created_at->format('M d, Y') }}</span>
                </div>
                <div>
                    <span class="text-sm text-gray-600">Due Date:</span>
                    <span class="font-medium">{{ $invoice->due_date ? $invoice->due_date->format('M d, Y') : 'N/A' }}</span>
                </div>
            </div>
        </div>

        <table class="w-full mb-8">
            <thead>
                <tr class="border-b">
                    <th class="px-4 py-3 text-left text-sm font-semibold text-gray-600">Description</th>
                    <th class="px-4 py-3 text-right text-sm font-semibold text-gray-600">Qty</th>
                    <th class="px-4 py-3 text-right text-sm font-semibold text-gray-600">Unit Price</th>
                    <th class="px-4 py-3 text-right text-sm font-semibold text-gray-600">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($invoice->items as $item)
                <tr class="border-b">
                    <td class="px-4 py-4">
                        <div class="font-medium text-gray-900">{{ $item->description }}</div>
                    </td>
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

        @if($invoice->payments->count() > 0)
        <div class="mt-8 pt-8 border-t">
            <h3 class="text-lg font-semibold mb-4">Payment History</h3>
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-2 text-left text-xs font-semibold">Date</th>
                        <th class="px-4 py-2 text-left text-xs font-semibold">Gateway</th>
                        <th class="px-4 py-2 text-left text-xs font-semibold">Transaction ID</th>
                        <th class="px-4 py-2 text-right text-xs font-semibold">Amount</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @foreach($invoice->payments as $payment)
                    <tr>
                        <td class="px-4 py-3 text-sm">{{ $payment->created_at->format('M d, Y H:i') }}</td>
                        <td class="px-4 py-3 text-sm">{{ ucfirst($payment->gateway) }}</td>
                        <td class="px-4 py-3 text-sm font-mono text-xs">{{ $payment->transaction_id }}</td>
                        <td class="px-4 py-3 text-right font-medium">${{ number_format($payment->amount, 2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>
</div>
@endsection
