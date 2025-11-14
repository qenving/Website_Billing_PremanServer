@extends('layouts.app-admin')

@section('title', 'Payment Details')

@section('content')
<div class="container mx-auto px-4 py-6 max-w-3xl">
    <div class="mb-6 flex items-center">
        <a href="{{ route('admin.payments.index') }}" class="text-gray-600 hover:text-gray-900 mr-4">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
        </a>
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Payment #{{ $payment->id }}</h1>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow-md p-8">
        <div class="flex justify-between items-start mb-8 pb-8 border-b">
            <div>
                <h2 class="text-2xl font-bold text-green-600 mb-2">${{ number_format($payment->amount, 2) }}</h2>
                <p class="text-gray-600">Payment received</p>
            </div>
            <span class="px-4 py-2 bg-green-100 text-green-800 rounded-full text-sm font-semibold">SUCCESS</span>
        </div>

        <div class="grid grid-cols-2 gap-8 mb-8">
            <div>
                <h3 class="text-sm font-semibold text-gray-600 uppercase mb-4">Payment Information</h3>
                <dl class="space-y-3">
                    <div>
                        <dt class="text-sm text-gray-500">Transaction ID</dt>
                        <dd class="font-mono text-sm mt-1">{{ $payment->transaction_id }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm text-gray-500">Payment Gateway</dt>
                        <dd class="font-medium mt-1">{{ ucfirst($payment->gateway) }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm text-gray-500">Payment Date</dt>
                        <dd class="font-medium mt-1">{{ $payment->created_at->format('M d, Y H:i:s') }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm text-gray-500">Status</dt>
                        <dd class="mt-1">
                            <span class="px-2 py-1 bg-green-100 text-green-800 rounded text-xs font-semibold">{{ ucfirst($payment->status) }}</span>
                        </dd>
                    </div>
                </dl>
            </div>

            <div>
                <h3 class="text-sm font-semibold text-gray-600 uppercase mb-4">Invoice & Client</h3>
                <dl class="space-y-3">
                    <div>
                        <dt class="text-sm text-gray-500">Invoice</dt>
                        <dd class="mt-1">
                            <a href="{{ route('admin.invoices.show', $payment->invoice_id) }}" class="text-blue-600 hover:underline font-medium">
                                #{{ $payment->invoice->invoice_number ?? $payment->invoice_id }}
                            </a>
                        </dd>
                    </div>
                    <div>
                        <dt class="text-sm text-gray-500">Client</dt>
                        <dd class="font-medium mt-1">{{ $payment->invoice->client->user->name ?? 'N/A' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm text-gray-500">Email</dt>
                        <dd class="mt-1">{{ $payment->invoice->client->user->email ?? 'N/A' }}</dd>
                    </div>
                </dl>
            </div>
        </div>

        @if($payment->metadata)
        <div class="mt-8 pt-8 border-t">
            <h3 class="text-sm font-semibold text-gray-600 uppercase mb-4">Payment Metadata</h3>
            <div class="bg-gray-50 rounded p-4">
                <pre class="text-xs font-mono">{{ json_encode(json_decode($payment->metadata), JSON_PRETTY_PRINT) }}</pre>
            </div>
        </div>
        @endif

        @if($payment->notes)
        <div class="mt-8 pt-8 border-t">
            <h3 class="text-sm font-semibold text-gray-600 uppercase mb-4">Notes</h3>
            <p class="text-gray-700">{{ $payment->notes }}</p>
        </div>
        @endif
    </div>
</div>
@endsection
