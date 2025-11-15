@extends('layouts.app-client')

@section('title', 'My Services')

@section('content')
<div class="container mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold text-gray-900 mb-6">My Services</h1>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <div class="bg-white rounded-lg shadow p-6">
            <p class="text-gray-500 text-sm">Active Services</p>
            <p class="text-3xl font-bold text-green-600 mt-2">{{ $activeCount ?? 0 }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-6">
            <p class="text-gray-500 text-sm">Total Spent</p>
            <p class="text-3xl font-bold text-blue-600 mt-2">${{ number_format($totalSpent ?? 0, 2) }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-6">
            <p class="text-gray-500 text-sm">Next Renewal</p>
            <p class="text-xl font-bold text-gray-900 mt-2">{{ $nextRenewal ?? 'N/A' }}</p>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow overflow-hidden">
        @forelse($services as $service)
        <div class="border-b last:border-b-0 p-6 hover:bg-gray-50">
            <div class="flex justify-between items-start">
                <div class="flex-1">
                    <div class="flex items-center mb-2">
                        <h3 class="text-lg font-semibold text-gray-900">{{ $service->product->name }}</h3>
                        @if($service->status == 'active')
                        <span class="ml-3 px-2 py-1 bg-green-100 text-green-800 text-xs rounded-full">Active</span>
                        @elseif($service->status == 'suspended')
                        <span class="ml-3 px-2 py-1 bg-yellow-100 text-yellow-800 text-xs rounded-full">Suspended</span>
                        @else
                        <span class="ml-3 px-2 py-1 bg-gray-100 text-gray-800 text-xs rounded-full">{{ ucfirst($service->status) }}</span>
                        @endif
                    </div>
                    @if($service->domain)
                    <p class="text-sm text-gray-600 mb-2">{{ $service->domain }}</p>
                    @endif
                    <div class="flex items-center space-x-4 text-sm text-gray-500">
                        <span>${{ number_format($service->price, 2) }}/{{ $service->billing_cycle }}</span>
                        <span>•</span>
                        <span>Next due: {{ $service->next_due_date ? $service->next_due_date->format('M d, Y') : 'N/A' }}</span>
                    </div>
                </div>
                <a href="{{ route('client.services.show', $service) }}" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg font-medium">
                    Manage
                </a>
            </div>
        </div>
        @empty
        <div class="p-12 text-center">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
            </svg>
            <p class="mt-4 text-gray-500 font-medium">No services yet</p>
            <p class="mt-2 text-sm text-gray-400">Order your first service to get started</p>
            <a href="{{ route('client.order.index') }}" class="mt-6 inline-block bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg font-semibold">
                Browse Products
            </a>
        </div>
        @endforelse
    </div>
</div>
@endsection
