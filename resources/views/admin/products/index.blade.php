@extends('layouts.app-admin')

@section('title', 'Products')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Products</h1>
            <p class="text-gray-600 mt-1">Manage your products and services</p>
        </div>
        <a href="{{ route('admin.products.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg font-semibold transition duration-200 inline-flex items-center">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
            </svg>
            Add Product
        </a>
    </div>

    @if(session('success'))
    <div class="mb-6 bg-green-50 border-l-4 border-green-500 p-4 rounded">
        <p class="text-green-700 font-medium">{{ session('success') }}</p>
    </div>
    @endif

    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search products..." class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
            <select name="group" class="px-4 py-2 border border-gray-300 rounded-lg">
                <option value="">All Groups</option>
                @foreach($productGroups ?? [] as $pg)
                <option value="{{ $pg->id }}" {{ request('group') == $pg->id ? 'selected' : '' }}>{{ $pg->name }}</option>
                @endforeach
            </select>
            <select name="status" class="px-4 py-2 border border-gray-300 rounded-lg">
                <option value="">All Status</option>
                <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
            </select>
            <button type="submit" class="bg-gray-700 hover:bg-gray-800 text-white px-6 py-2 rounded-lg font-semibold">Filter</button>
        </form>
    </div>

    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <table class="w-full">
            <thead class="bg-gray-50 border-b">
                <tr>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase">Product</th>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase">Group</th>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase">Price</th>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase">Type</th>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase">Status</th>
                    <th class="px-6 py-4 text-right text-xs font-semibold text-gray-600 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($products as $product)
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4">
                        <div class="font-medium text-gray-900">{{ $product->name }}</div>
                        <div class="text-sm text-gray-500">{{ Str::limit($product->description, 50) }}</div>
                    </td>
                    <td class="px-6 py-4 text-sm">{{ $product->productGroup->name ?? 'N/A' }}</td>
                    <td class="px-6 py-4">
                        <span class="font-semibold text-gray-900">${{ number_format($product->price, 2) }}</span>
                        <span class="text-xs text-gray-500">/{{ $product->billing_cycle }}</span>
                    </td>
                    <td class="px-6 py-4">
                        <span class="px-2 py-1 bg-purple-100 text-purple-800 text-xs rounded-full">{{ ucfirst($product->type) }}</span>
                    </td>
                    <td class="px-6 py-4">
                        @if($product->is_active)
                        <span class="px-3 py-1 bg-green-100 text-green-800 text-xs rounded-full font-semibold">Active</span>
                        @else
                        <span class="px-3 py-1 bg-red-100 text-red-800 text-xs rounded-full font-semibold">Inactive</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 text-right">
                        <div class="flex justify-end space-x-2">
                            <a href="{{ route('admin.products.show', $product) }}" class="text-blue-600 hover:text-blue-900" title="View">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            </a>
                            <a href="{{ route('admin.products.edit', $product) }}" class="text-yellow-600 hover:text-yellow-900" title="Edit">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                            </a>
                            <form method="POST" action="{{ route('admin.products.toggle-status', $product) }}" class="inline">
                                @csrf
                                <button type="submit" class="{{ $product->is_active ? 'text-red-600 hover:text-red-900' : 'text-green-600 hover:text-green-900' }}" title="Toggle">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                                </button>
                            </form>
                            <form method="POST" action="{{ route('admin.products.destroy', $product) }}" onsubmit="return confirm('Delete this product?')" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:text-red-900" title="Delete">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                        <p class="text-lg font-medium">No products found</p>
                        <p class="mt-2">Create your first product to get started.</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
        @if($products->hasPages())
        <div class="bg-gray-50 px-6 py-4 border-t">{{ $products->links() }}</div>
        @endif
    </div>
</div>
@endsection
