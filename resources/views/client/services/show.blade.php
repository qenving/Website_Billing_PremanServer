@extends('layouts.app-client')

@section('title', 'Service Details')

@section('content')
<div class="container mx-auto px-4 py-8 max-w-4xl">
    <div class="mb-6">
        <a href="{{ route('client.services.index') }}" class="text-blue-600 hover:text-blue-800 text-sm font-medium">← Back to Services</a>
        <h1 class="text-3xl font-bold text-gray-900 mt-2">{{ $service->product->name }}</h1>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-lg font-semibold mb-4">Service Information</h2>
                <dl class="grid grid-cols-2 gap-4">
                    <div>
                        <dt class="text-sm text-gray-500">Status</dt>
                        <dd class="mt-1">
                            @if($service->status == 'active')
                            <span class="px-3 py-1 bg-green-100 text-green-800 rounded-full text-sm font-semibold">Active</span>
                            @else
                            <span class="px-3 py-1 bg-yellow-100 text-yellow-800 rounded-full text-sm font-semibold">{{ ucfirst($service->status) }}</span>
                            @endif
                        </dd>
                    </div>
                    <div>
                        <dt class="text-sm text-gray-500">Domain</dt>
                        <dd class="font-medium mt-1">{{ $service->domain ?? 'N/A' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm text-gray-500">Price</dt>
                        <dd class="font-medium mt-1">${{ number_format($service->price, 2) }}/{{ $service->billing_cycle }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm text-gray-500">Next Due Date</dt>
                        <dd class="font-medium mt-1">{{ $service->next_due_date ? $service->next_due_date->format('M d, Y') : 'N/A' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm text-gray-500">Registration Date</dt>
                        <dd class="font-medium mt-1">{{ $service->created_at->format('M d, Y') }}</dd>
                    </div>
                </dl>
            </div>

            @if($service->config_options)
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-lg font-semibold mb-4">Configuration</h2>
                <dl class="grid grid-cols-2 gap-4">
                    @foreach(json_decode($service->config_options, true) ?? [] as $key => $value)
                    <div>
                        <dt class="text-sm text-gray-500">{{ ucwords(str_replace('_', ' ', $key)) }}</dt>
                        <dd class="font-medium mt-1">{{ $value }}</dd>
                    </div>
                    @endforeach
                </dl>
            </div>
            @endif
        </div>

        <div class="lg:col-span-1">
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-lg font-semibold mb-4">Actions</h2>
                <div class="space-y-2">
                    @if($service->status == 'active')
                    <form method="POST" action="{{ route('client.services.action', ['service' => $service, 'action' => 'request_cancellation']) }}">
                        @csrf
                        <button type="submit" class="w-full text-left px-4 py-2 text-sm text-red-700 hover:bg-red-50 rounded-lg">Request Cancellation</button>
                    </form>
                    @endif
                    <a href="{{ route('client.tickets.create', ['service' => $service->id]) }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 rounded-lg">Open Support Ticket</a>
                </div>
            </div>

            <div class="bg-blue-50 rounded-lg p-4 mt-4">
                <p class="text-sm text-blue-800"><strong>Need help?</strong> Contact our support team anytime.</p>
            </div>
        </div>
    </div>
</div>
@endsection
