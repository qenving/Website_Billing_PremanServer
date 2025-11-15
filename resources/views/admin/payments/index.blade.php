@extends('layouts.app-admin')

@section('title', 'Payments')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-900">Payment History</h1>
        <p class="text-gray-600 mt-1">View all payment transactions</p>
    </div>

    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search..." class="px-4 py-2 border rounded-lg">
            <select name="gateway" class="px-4 py-2 border rounded-lg">
                <option value="">All Gateways</option>
                <option value="stripe">Stripe</option>
                <option value="paypal">PayPal</option>
                <option value="midtrans">Midtrans</option>
                <option value="xendit">Xendit</option>
            </select>
            <input type="date" name="date_from" value="{{ request('date_from') }}" class="px-4 py-2 border rounded-lg">
            <button type="submit" class="bg-gray-700 hover:bg-gray-800 text-white px-6 py-2 rounded-lg font-semibold">Filter</button>
        </form>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-lg shadow-md p-6">
            <p class="text-gray-500 text-sm">Total Payments</p>
            <p class="text-3xl font-bold text-gray-900 mt-2">{{ $totalPayments ?? 0 }}</p>
        </div>
        <div class="bg-white rounded-lg shadow-md p-6">
            <p class="text-gray-500 text-sm">Today</p>
            <p class="text-3xl font-bold text-green-600 mt-2">${{ number_format($todayAmount ?? 0, 2) }}</p>
        </div>
        <div class="bg-white rounded-lg shadow-md p-6">
            <p class="text-gray-500 text-sm">This Month</p>
            <p class="text-3xl font-bold text-blue-600 mt-2">${{ number_format($monthAmount ?? 0, 2) }}</p>
        </div>
        <div class="bg-white rounded-lg shadow-md p-6">
            <p class="text-gray-500 text-sm">This Year</p>
            <p class="text-3xl font-bold text-purple-600 mt-2">${{ number_format($yearAmount ?? 0, 2) }}</p>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <table class="w-full">
            <thead class="bg-gray-50 border-b">
                <tr>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase">ID</th>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase">Client</th>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase">Invoice</th>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase">Amount</th>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase">Gateway</th>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase">Transaction ID</th>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase">Date</th>
                    <th class="px-6 py-4 text-right text-xs font-semibold text-gray-600 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @forelse($payments as $payment)
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 text-sm font-medium">#{{ $payment->id }}</td>
                    <td class="px-6 py-4 text-sm">{{ $payment->invoice->client->user->name ?? 'N/A' }}</td>
                    <td class="px-6 py-4 text-sm">
                        <a href="{{ route('admin.invoices.show', $payment->invoice_id) }}" class="text-blue-600 hover:underline">
                            #{{ $payment->invoice->invoice_number ?? $payment->invoice_id }}
                        </a>
                    </td>
                    <td class="px-6 py-4">
                        <span class="font-semibold text-green-600">${{ number_format($payment->amount, 2) }}</span>
                    </td>
                    <td class="px-6 py-4">
                        <span class="px-2 py-1 bg-blue-100 text-blue-800 text-xs rounded-full">{{ ucfirst($payment->gateway) }}</span>
                    </td>
                    <td class="px-6 py-4 text-sm font-mono text-xs">{{ Str::limit($payment->transaction_id, 20) }}</td>
                    <td class="px-6 py-4 text-sm">{{ $payment->created_at->format('M d, Y H:i') }}</td>
                    <td class="px-6 py-4 text-right">
                        <a href="{{ route('admin.payments.show', $payment) }}" class="text-blue-600 hover:text-blue-900" title="View">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                        </a>
                    </td>
                </tr>
                @empty
                <tr><td colspan="8" class="px-6 py-12 text-center text-gray-500">No payments found</td></tr>
                @endforelse
            </tbody>
        </table>
        @if($payments->hasPages())
        <div class="bg-gray-50 px-6 py-4 border-t">{{ $payments->links() }}</div>
        @endif
    </div>
</div>
@endsection
