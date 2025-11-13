@extends('layouts.app-client')

@section('title', 'Dashboard')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Page Header -->
    <div class="md:flex md:items-center md:justify-between">
        <div class="flex-1 min-w-0">
            <h2 class="text-2xl font-bold leading-7 text-gray-900 sm:text-3xl sm:truncate">
                Welcome back, {{ auth()->user()->name }}!
            </h2>
        </div>
    </div>

    <!-- Stats Grid -->
    <div class="mt-8 grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3">
        <!-- Active Services -->
        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-8 w-8 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2m-2-4h.01M17 16h.01"/>
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Active Services</dt>
                            <dd class="flex items-baseline">
                                <div class="text-3xl font-semibold text-gray-900">
                                    {{ $stats['active_services'] }}
                                </div>
                                <div class="ml-2 text-sm text-gray-500">
                                    / {{ $stats['total_services'] }}
                                </div>
                            </dd>
                        </dl>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 px-5 py-3">
                <div class="text-sm">
                    <a href="{{ route('client.services.index') }}" class="font-medium text-primary hover:text-primary-dark">
                        View all services →
                    </a>
                </div>
            </div>
        </div>

        <!-- Unpaid Invoices -->
        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-8 w-8 {{ $stats['unpaid_invoices'] > 0 ? 'text-yellow-500' : 'text-green-500' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Unpaid Invoices</dt>
                            <dd class="text-3xl font-semibold text-gray-900">{{ $stats['unpaid_invoices'] }}</dd>
                            @if($stats['unpaid_amount'] > 0)
                            <dd class="mt-1 text-sm text-gray-500">
                                Total: ${{ number_format($stats['unpaid_amount'], 2) }}
                            </dd>
                            @endif
                        </dl>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 px-5 py-3">
                <div class="text-sm">
                    <a href="{{ route('client.invoices.index', ['status' => 'unpaid']) }}" class="font-medium text-primary hover:text-primary-dark">
                        Pay invoices →
                    </a>
                </div>
            </div>
        </div>

        <!-- Open Tickets -->
        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-8 w-8 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"/>
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Open Tickets</dt>
                            <dd class="text-3xl font-semibold text-gray-900">{{ $stats['open_tickets'] }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 px-5 py-3">
                <div class="text-sm">
                    <a href="{{ route('client.tickets.index') }}" class="font-medium text-primary hover:text-primary-dark">
                        View tickets →
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Credit Balance (if applicable) -->
    @if($stats['credit_balance'] > 0)
    <div class="mt-5">
        <div class="bg-green-50 border border-green-200 rounded-lg p-4">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div class="ml-3 flex-1">
                    <h3 class="text-sm font-medium text-green-800">Account Credit Available</h3>
                    <div class="mt-2 text-sm text-green-700">
                        <p>You have <span class="font-bold">${{ number_format($stats['credit_balance'], 2) }}</span> credit balance that can be applied to your next purchase or invoice payment.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Quick Actions -->
    <div class="mt-8">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Quick Actions</h3>
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <a href="{{ route('client.orders.index') }}" class="relative rounded-lg border border-gray-300 bg-white px-6 py-5 shadow-sm flex items-center space-x-3 hover:border-primary focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-primary">
                <div class="flex-shrink-0">
                    <svg class="h-6 w-6 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                </div>
                <div class="flex-1 min-w-0">
                    <span class="absolute inset-0" aria-hidden="true"></span>
                    <p class="text-sm font-medium text-gray-900">Order Services</p>
                </div>
            </a>

            <a href="{{ route('client.tickets.create') }}" class="relative rounded-lg border border-gray-300 bg-white px-6 py-5 shadow-sm flex items-center space-x-3 hover:border-primary focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-primary">
                <div class="flex-shrink-0">
                    <svg class="h-6 w-6 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                </div>
                <div class="flex-1 min-w-0">
                    <span class="absolute inset-0" aria-hidden="true"></span>
                    <p class="text-sm font-medium text-gray-900">Open Ticket</p>
                </div>
            </a>

            <a href="{{ route('client.invoices.index') }}" class="relative rounded-lg border border-gray-300 bg-white px-6 py-5 shadow-sm flex items-center space-x-3 hover:border-primary focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-primary">
                <div class="flex-shrink-0">
                    <svg class="h-6 w-6 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
                <div class="flex-1 min-w-0">
                    <span class="absolute inset-0" aria-hidden="true"></span>
                    <p class="text-sm font-medium text-gray-900">View Invoices</p>
                </div>
            </a>

            <a href="{{ route('client.account.profile') }}" class="relative rounded-lg border border-gray-300 bg-white px-6 py-5 shadow-sm flex items-center space-x-3 hover:border-primary focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-primary">
                <div class="flex-shrink-0">
                    <svg class="h-6 w-6 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                </div>
                <div class="flex-1 min-w-0">
                    <span class="absolute inset-0" aria-hidden="true"></span>
                    <p class="text-sm font-medium text-gray-900">My Account</p>
                </div>
            </a>
        </div>
    </div>

    <!-- Recent Activity -->
    <div class="mt-8 grid grid-cols-1 gap-5 lg:grid-cols-2">
        <!-- Recent Services -->
        <div class="bg-white shadow rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Recent Services</h3>
                @forelse($recentServices as $service)
                <div class="flex items-center justify-between py-3 border-b border-gray-200 last:border-0">
                    <div class="flex-1">
                        <p class="text-sm font-medium text-gray-900">{{ $service->product->name }}</p>
                        <p class="text-xs text-gray-500">{{ $service->domain ?? 'No domain' }}</p>
                    </div>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                        {{ $service->status === 'active' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                        {{ ucfirst($service->status) }}
                    </span>
                </div>
                @empty
                <p class="text-sm text-gray-500">No services yet. <a href="{{ route('client.orders.index') }}" class="text-primary hover:text-primary-dark">Order your first service →</a></p>
                @endforelse
            </div>
        </div>

        <!-- Unpaid Invoices List -->
        <div class="bg-white shadow rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Unpaid Invoices</h3>
                @forelse($unpaidInvoices as $invoice)
                <div class="flex items-center justify-between py-3 border-b border-gray-200 last:border-0">
                    <div class="flex-1">
                        <p class="text-sm font-medium text-gray-900">{{ $invoice->invoice_number }}</p>
                        <p class="text-xs text-gray-500">Due: {{ $invoice->due_date->format('M d, Y') }}</p>
                    </div>
                    <div class="text-right">
                        <p class="text-sm font-semibold text-gray-900">${{ number_format($invoice->total, 2) }}</p>
                        <a href="{{ route('client.invoices.pay', $invoice) }}" class="text-xs text-primary hover:text-primary-dark">Pay now →</a>
                    </div>
                </div>
                @empty
                <p class="text-sm text-gray-500">No unpaid invoices. You're all caught up!</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
