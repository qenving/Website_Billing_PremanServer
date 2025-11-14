@extends('layouts.app-admin')

@section('title', 'Invoices')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Invoices</h1>
            <p class="text-gray-600 mt-1">Manage all invoices and billing</p>
        </div>
        <a href="{{ route('admin.invoices.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg font-semibold inline-flex items-center">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
            Create Invoice
        </a>
    </div>

    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <form method="GET" class="grid grid-cols-1 md:grid-cols-5 gap-4">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search..." class="px-4 py-2 border rounded-lg">
            <select name="status" class="px-4 py-2 border rounded-lg">
                <option value="">All Status</option>
                <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                <option value="unpaid" {{ request('status') == 'unpaid' ? 'selected' : '' }}>Unpaid</option>
                <option value="paid" {{ request('status') == 'paid' ? 'selected' : '' }}>Paid</option>
                <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
            </select>
            <input type="date" name="date_from" value="{{ request('date_from') }}" placeholder="From" class="px-4 py-2 border rounded-lg">
            <input type="date" name="date_to" value="{{ request('date_to') }}" placeholder="To" class="px-4 py-2 border rounded-lg">
            <button type="submit" class="bg-gray-700 hover:bg-gray-800 text-white px-6 py-2 rounded-lg font-semibold">Filter</button>
        </form>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-lg shadow-md p-6">
            <p class="text-gray-500 text-sm">Total Invoices</p>
            <p class="text-3xl font-bold text-gray-900 mt-2">{{ $totalInvoices ?? 0 }}</p>
        </div>
        <div class="bg-white rounded-lg shadow-md p-6">
            <p class="text-gray-500 text-sm">Unpaid</p>
            <p class="text-3xl font-bold text-red-600 mt-2">${{ number_format($unpaidAmount ?? 0, 2) }}</p>
        </div>
        <div class="bg-white rounded-lg shadow-md p-6">
            <p class="text-gray-500 text-sm">Paid This Month</p>
            <p class="text-3xl font-bold text-green-600 mt-2">${{ number_format($paidThisMonth ?? 0, 2) }}</p>
        </div>
        <div class="bg-white rounded-lg shadow-md p-6">
            <p class="text-gray-500 text-sm">Overdue</p>
            <p class="text-3xl font-bold text-yellow-600 mt-2">{{ $overdueCount ?? 0 }}</p>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <table class="w-full">
            <thead class="bg-gray-50 border-b">
                <tr>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase">Invoice</th>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase">Client</th>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase">Amount</th>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase">Status</th>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase">Due Date</th>
                    <th class="px-6 py-4 text-right text-xs font-semibold text-gray-600 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @forelse($invoices as $invoice)
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4">
                        <div class="font-medium text-gray-900">#{{ $invoice->invoice_number }}</div>
                        <div class="text-xs text-gray-500">{{ $invoice->created_at->format('M d, Y') }}</div>
                    </td>
                    <td class="px-6 py-4 text-sm">{{ $invoice->client->user->name ?? 'N/A' }}</td>
                    <td class="px-6 py-4">
                        <span class="font-semibold text-gray-900">${{ number_format($invoice->total, 2) }}</span>
                    </td>
                    <td class="px-6 py-4">
                        @if($invoice->status == 'paid')
                        <span class="px-2 py-1 bg-green-100 text-green-800 text-xs rounded-full font-semibold">Paid</span>
                        @elseif($invoice->status == 'unpaid')
                        <span class="px-2 py-1 bg-red-100 text-red-800 text-xs rounded-full font-semibold">Unpaid</span>
                        @elseif($invoice->status == 'cancelled')
                        <span class="px-2 py-1 bg-gray-100 text-gray-800 text-xs rounded-full font-semibold">Cancelled</span>
                        @else
                        <span class="px-2 py-1 bg-yellow-100 text-yellow-800 text-xs rounded-full font-semibold">{{ ucfirst($invoice->status) }}</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 text-sm">
                        @if($invoice->due_date)
                        {{ $invoice->due_date->format('M d, Y') }}
                        @if($invoice->due_date->isPast() && $invoice->status == 'unpaid')
                        <span class="ml-2 text-red-600 text-xs">(Overdue)</span>
                        @endif
                        @else
                        N/A
                        @endif
                    </td>
                    <td class="px-6 py-4 text-right">
                        <div class="flex justify-end space-x-2">
                            <a href="{{ route('admin.invoices.show', $invoice) }}" class="text-blue-600 hover:text-blue-900" title="View">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            </a>
                            <a href="{{ route('admin.invoices.edit', $invoice) }}" class="text-yellow-600 hover:text-yellow-900" title="Edit">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                            </a>
                            @if($invoice->status == 'unpaid')
                            <form method="POST" action="{{ route('admin.invoices.send', $invoice) }}" class="inline">
                                @csrf
                                <button type="submit" class="text-green-600 hover:text-green-900" title="Send">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                                </button>
                            </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="px-6 py-12 text-center text-gray-500">No invoices found</td></tr>
                @endforelse
            </tbody>
        </table>
        @if($invoices->hasPages())
        <div class="bg-gray-50 px-6 py-4 border-t">{{ $invoices->links() }}</div>
        @endif
    </div>
</div>
@endsection
