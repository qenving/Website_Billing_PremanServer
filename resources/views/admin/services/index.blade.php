@extends('layouts.app-admin')

@section('title', 'Services')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-900">Services</h1>
        <p class="text-gray-600 mt-1">Manage all client services and subscriptions</p>
    </div>

    @if(session('success'))
    <div class="mb-6 bg-green-50 border-l-4 border-green-500 p-4 rounded">
        <p class="text-green-700 font-medium">{{ session('success') }}</p>
    </div>
    @endif

    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <form method="GET" class="grid grid-cols-1 md:grid-cols-5 gap-4">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search..." class="px-4 py-2 border rounded-lg">
            <select name="status" class="px-4 py-2 border rounded-lg">
                <option value="">All Status</option>
                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                <option value="suspended" {{ request('status') == 'suspended' ? 'selected' : '' }}>Suspended</option>
                <option value="terminated" {{ request('status') == 'terminated' ? 'selected' : '' }}>Terminated</option>
            </select>
            <select name="product" class="px-4 py-2 border rounded-lg">
                <option value="">All Products</option>
                @foreach($products ?? [] as $p)
                <option value="{{ $p->id }}" {{ request('product') == $p->id ? 'selected' : '' }}>{{ $p->name }}</option>
                @endforeach
            </select>
            <select name="billing" class="px-4 py-2 border rounded-lg">
                <option value="">All Cycles</option>
                <option value="monthly">Monthly</option>
                <option value="quarterly">Quarterly</option>
                <option value="annually">Annually</option>
            </select>
            <button type="submit" class="bg-gray-700 hover:bg-gray-800 text-white px-6 py-2 rounded-lg font-semibold">Filter</button>
        </form>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-lg shadow-md p-6">
            <p class="text-gray-500 text-sm">Total Services</p>
            <p class="text-3xl font-bold text-gray-900 mt-2">{{ $totalServices ?? 0 }}</p>
        </div>
        <div class="bg-white rounded-lg shadow-md p-6">
            <p class="text-gray-500 text-sm">Active</p>
            <p class="text-3xl font-bold text-green-600 mt-2">{{ $activeServices ?? 0 }}</p>
        </div>
        <div class="bg-white rounded-lg shadow-md p-6">
            <p class="text-gray-500 text-sm">Suspended</p>
            <p class="text-3xl font-bold text-yellow-600 mt-2">{{ $suspendedServices ?? 0 }}</p>
        </div>
        <div class="bg-white rounded-lg shadow-md p-6">
            <p class="text-gray-500 text-sm">MRR</p>
            <p class="text-3xl font-bold text-blue-600 mt-2">${{ number_format($mrr ?? 0, 2) }}</p>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <table class="w-full">
            <thead class="bg-gray-50 border-b">
                <tr>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase">Service</th>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase">Client</th>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase">Product</th>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase">Price</th>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase">Status</th>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase">Next Due</th>
                    <th class="px-6 py-4 text-right text-xs font-semibold text-gray-600 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @forelse($services as $service)
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4">
                        <div class="font-medium text-gray-900">#{{ $service->id }}</div>
                        <div class="text-xs text-gray-500">{{ $service->domain ?? 'N/A' }}</div>
                    </td>
                    <td class="px-6 py-4 text-sm">{{ $service->client->user->name ?? 'N/A' }}</td>
                    <td class="px-6 py-4 text-sm">{{ $service->product->name ?? 'N/A' }}</td>
                    <td class="px-6 py-4">
                        <span class="font-semibold">${{ number_format($service->price, 2) }}</span>
                        <span class="text-xs text-gray-500">/{{ $service->billing_cycle }}</span>
                    </td>
                    <td class="px-6 py-4">
                        @if($service->status == 'active')
                        <span class="px-2 py-1 bg-green-100 text-green-800 text-xs rounded-full">Active</span>
                        @elseif($service->status == 'suspended')
                        <span class="px-2 py-1 bg-yellow-100 text-yellow-800 text-xs rounded-full">Suspended</span>
                        @elseif($service->status == 'terminated')
                        <span class="px-2 py-1 bg-red-100 text-red-800 text-xs rounded-full">Terminated</span>
                        @else
                        <span class="px-2 py-1 bg-gray-100 text-gray-800 text-xs rounded-full">{{ ucfirst($service->status) }}</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 text-sm">{{ $service->next_due_date ? $service->next_due_date->format('M d, Y') : 'N/A' }}</td>
                    <td class="px-6 py-4 text-right">
                        <div class="flex justify-end space-x-2">
                            <a href="{{ route('admin.services.show', $service) }}" class="text-blue-600 hover:text-blue-900" title="View">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            </a>
                            <a href="{{ route('admin.services.edit', $service) }}" class="text-yellow-600 hover:text-yellow-900" title="Edit">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                            </a>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" class="px-6 py-12 text-center text-gray-500">No services found</td></tr>
                @endforelse
            </tbody>
        </table>
        @if($services->hasPages())
        <div class="bg-gray-50 px-6 py-4 border-t">{{ $services->links() }}</div>
        @endif
    </div>
</div>
@endsection
