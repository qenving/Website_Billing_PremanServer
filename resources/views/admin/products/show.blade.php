@extends('layouts.app-admin')

@section('title', 'Product Details')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="mb-6 flex items-center justify-between">
        <div class="flex items-center">
            <a href="{{ route('admin.products.index') }}" class="text-gray-600 hover:text-gray-900 mr-4">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            </a>
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Product Details</h1>
            </div>
        </div>
        <a href="{{ route('admin.products.edit', $product) }}" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg font-semibold">Edit</a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-1 bg-white rounded-lg shadow-md p-6">
            <h2 class="text-2xl font-bold text-gray-900 mb-4">{{ $product->name }}</h2>
            <div class="space-y-4">
                <div>
                    <p class="text-sm text-gray-500">Status</p>
                    @if($product->is_active)
                    <span class="px-3 py-1 bg-green-100 text-green-800 rounded-full text-sm font-semibold">Active</span>
                    @else
                    <span class="px-3 py-1 bg-red-100 text-red-800 rounded-full text-sm font-semibold">Inactive</span>
                    @endif
                </div>
                <div>
                    <p class="text-sm text-gray-500">Group</p>
                    <p class="font-medium">{{ $product->productGroup->name ?? 'N/A' }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Type</p>
                    <p class="font-medium">{{ ucfirst($product->type) }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Price</p>
                    <p class="text-2xl font-bold text-green-600">${{ number_format($product->price, 2) }}</p>
                    <p class="text-sm text-gray-500">per {{ $product->billing_cycle }}</p>
                </div>
                @if($product->setup_fee > 0)
                <div>
                    <p class="text-sm text-gray-500">Setup Fee</p>
                    <p class="font-medium">${{ number_format($product->setup_fee, 2) }}</p>
                </div>
                @endif
            </div>
        </div>

        <div class="lg:col-span-2 space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="bg-white rounded-lg shadow-md p-6">
                    <p class="text-gray-500 text-sm">Active Services</p>
                    <p class="text-3xl font-bold text-gray-900 mt-2">{{ $product->services()->where('status', 'active')->count() }}</p>
                </div>
                <div class="bg-white rounded-lg shadow-md p-6">
                    <p class="text-gray-500 text-sm">Total Revenue</p>
                    <p class="text-3xl font-bold text-green-600 mt-2">${{ number_format($product->services()->sum('price'), 2) }}</p>
                </div>
                <div class="bg-white rounded-lg shadow-md p-6">
                    <p class="text-gray-500 text-sm">Total Orders</p>
                    <p class="text-3xl font-bold text-blue-600 mt-2">{{ $product->services->count() }}</p>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-md p-6">
                <h3 class="text-lg font-semibold mb-4">Description</h3>
                <p class="text-gray-700">{{ $product->description ?? 'No description.' }}</p>
            </div>

            <div class="bg-white rounded-lg shadow-md p-6">
                <h3 class="text-lg font-semibold mb-4">Recent Services</h3>
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-2 text-left text-xs font-semibold">Client</th>
                            <th class="px-4 py-2 text-left text-xs font-semibold">Status</th>
                            <th class="px-4 py-2 text-left text-xs font-semibold">Date</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        @forelse($product->services()->latest()->limit(5)->get() as $service)
                        <tr>
                            <td class="px-4 py-3 text-sm">{{ $service->client->user->name ?? 'N/A' }}</td>
                            <td class="px-4 py-3"><span class="px-2 py-1 bg-green-100 text-green-800 text-xs rounded">{{ $service->status }}</span></td>
                            <td class="px-4 py-3 text-sm">{{ $service->created_at->format('M d, Y') }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="3" class="px-4 py-8 text-center text-gray-500">No services yet</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
