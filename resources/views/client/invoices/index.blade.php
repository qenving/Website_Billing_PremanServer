@extends('layouts.app-client')

@section('title', 'My Invoices')

@section('content')
<div class="container mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold text-gray-900 mb-6">My Invoices</h1>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <div class="bg-white rounded-lg shadow p-6">
            <p class="text-gray-500 text-sm">Total Invoices</p>
            <p class="text-3xl font-bold text-gray-900 mt-2">{{ $totalInvoices ?? 0 }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-6">
            <p class="text-gray-500 text-sm">Unpaid</p>
            <p class="text-3xl font-bold text-red-600 mt-2">${{ number_format($unpaidAmount ?? 0, 2) }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-6">
            <p class="text-gray-500 text-sm">Paid This Year</p>
            <p class="text-3xl font-bold text-green-600 mt-2">${{ number_format($paidThisYear ?? 0, 2) }}</p>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="w-full">
            <thead class="bg-gray-50 border-b">
                <tr>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase">Invoice</th>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase">Amount</th>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase">Status</th>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase">Due Date</th>
                    <th class="px-6 py-4 text-right text-xs font-semibold text-gray-600 uppercase">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @forelse($invoices as $invoice)
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4">
                        <div class="font-medium text-gray-900">#{{ $invoice->invoice_number }}</div>
                        <div class="text-xs text-gray-500">{{ $invoice->created_at->format('M d, Y') }}</div>
                    </td>
                    <td class="px-6 py-4 font-semibold">${{ number_format($invoice->total, 2) }}</td>
                    <td class="px-6 py-4">
                        @if($invoice->status == 'paid')
                        <span class="px-2 py-1 bg-green-100 text-green-800 text-xs rounded-full font-semibold">Paid</span>
                        @elseif($invoice->status == 'unpaid')
                        <span class="px-2 py-1 bg-red-100 text-red-800 text-xs rounded-full font-semibold">Unpaid</span>
                        @else
                        <span class="px-2 py-1 bg-gray-100 text-gray-800 text-xs rounded-full font-semibold">{{ ucfirst($invoice->status) }}</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 text-sm">
                        {{ $invoice->due_date ? $invoice->due_date->format('M d, Y') : 'N/A' }}
                        @if($invoice->due_date && $invoice->due_date->isPast() && $invoice->status == 'unpaid')
                        <span class="text-red-600 text-xs">(Overdue)</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 text-right space-x-2">
                        <a href="{{ route('client.invoices.show', $invoice) }}" class="text-blue-600 hover:text-blue-800 text-sm font-medium">View</a>
                        @if($invoice->status == 'unpaid')
                        <a href="{{ route('client.invoices.show', $invoice) }}" class="text-green-600 hover:text-green-800 text-sm font-medium">Pay Now</a>
                        @endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="5" class="px-6 py-12 text-center text-gray-500">No invoices yet</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
