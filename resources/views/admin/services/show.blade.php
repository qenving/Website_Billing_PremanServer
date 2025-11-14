@extends('layouts.app-admin')

@section('title', 'Service Details')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="mb-6 flex justify-between items-center">
        <div class="flex items-center">
            <a href="{{ route('admin.services.index') }}" class="text-gray-600 hover:text-gray-900 mr-4">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            </a>
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Service #{{ $service->id }}</h1>
                <p class="text-gray-600 mt-1">{{ $service->product->name ?? 'N/A' }}</p>
            </div>
        </div>
        <div class="flex space-x-2">
            <a href="{{ route('admin.services.edit', $service) }}" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg font-semibold">Edit</a>
            @if($service->status == 'active')
            <form method="POST" action="{{ route('admin.services.suspend', $service) }}" class="inline">
                @csrf
                <button type="submit" class="bg-yellow-600 hover:bg-yellow-700 text-white px-6 py-2 rounded-lg font-semibold">Suspend</button>
            </form>
            @elseif($service->status == 'suspended')
            <form method="POST" action="{{ route('admin.services.unsuspend', $service) }}" class="inline">
                @csrf
                <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded-lg font-semibold">Unsuspend</button>
            </form>
            @endif
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-1 space-y-6">
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-lg font-semibold mb-4">Service Information</h2>
                <dl class="space-y-3">
                    <div>
                        <dt class="text-sm text-gray-500">Status</dt>
                        <dd class="mt-1">
                            @if($service->status == 'active')
                            <span class="px-3 py-1 bg-green-100 text-green-800 rounded-full text-sm font-semibold">Active</span>
                            @elseif($service->status == 'suspended')
                            <span class="px-3 py-1 bg-yellow-100 text-yellow-800 rounded-full text-sm font-semibold">Suspended</span>
                            @else
                            <span class="px-3 py-1 bg-gray-100 text-gray-800 rounded-full text-sm font-semibold">{{ ucfirst($service->status) }}</span>
                            @endif
                        </dd>
                    </div>
                    <div>
                        <dt class="text-sm text-gray-500">Client</dt>
                        <dd class="font-medium">{{ $service->client->user->name ?? 'N/A' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm text-gray-500">Product</dt>
                        <dd class="font-medium">{{ $service->product->name ?? 'N/A' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm text-gray-500">Domain</dt>
                        <dd class="font-medium">{{ $service->domain ?? 'N/A' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm text-gray-500">Price</dt>
                        <dd class="text-xl font-bold text-green-600">${{ number_format($service->price, 2) }}</dd>
                        <p class="text-xs text-gray-500">per {{ $service->billing_cycle }}</p>
                    </div>
                    <div>
                        <dt class="text-sm text-gray-500">Registration Date</dt>
                        <dd class="font-medium">{{ $service->created_at->format('M d, Y') }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm text-gray-500">Next Due Date</dt>
                        <dd class="font-medium">{{ $service->next_due_date ? $service->next_due_date->format('M d, Y') : 'N/A' }}</dd>
                    </div>
                </dl>
            </div>

            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-lg font-semibold mb-4">Actions</h2>
                <div class="space-y-2">
                    @if($service->status == 'pending')
                    <form method="POST" action="{{ route('admin.services.provision', $service) }}">
                        @csrf
                        <button type="submit" class="w-full text-left px-4 py-2 text-sm text-green-700 hover:bg-green-50 rounded-lg">Provision Service</button>
                    </form>
                    @endif
                    <form method="POST" action="{{ route('admin.services.terminate', $service) }}" onsubmit="return confirm('Terminate this service?')">
                        @csrf
                        <button type="submit" class="w-full text-left px-4 py-2 text-sm text-red-700 hover:bg-red-50 rounded-lg">Terminate Service</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-lg font-semibold mb-4">Recent Invoices</h2>
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-2 text-left text-xs font-semibold">Invoice</th>
                            <th class="px-4 py-2 text-left text-xs font-semibold">Amount</th>
                            <th class="px-4 py-2 text-left text-xs font-semibold">Status</th>
                            <th class="px-4 py-2 text-left text-xs font-semibold">Date</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        @forelse($service->invoices ?? [] as $invoice)
                        <tr>
                            <td class="px-4 py-3 text-sm">#{{ $invoice->id }}</td>
                            <td class="px-4 py-3 text-sm font-medium">${{ number_format($invoice->total, 2) }}</td>
                            <td class="px-4 py-3"><span class="px-2 py-1 bg-green-100 text-green-800 text-xs rounded">{{ $invoice->status }}</span></td>
                            <td class="px-4 py-3 text-sm">{{ $invoice->created_at->format('M d, Y') }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="4" class="px-4 py-8 text-center text-gray-500">No invoices</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($service->config_options)
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-lg font-semibold mb-4">Configuration</h2>
                <dl class="grid grid-cols-2 gap-4">
                    @foreach(json_decode($service->config_options, true) ?? [] as $key => $value)
                    <div>
                        <dt class="text-sm text-gray-500">{{ ucwords(str_replace('_', ' ', $key)) }}</dt>
                        <dd class="font-medium">{{ $value }}</dd>
                    </div>
                    @endforeach
                </dl>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
