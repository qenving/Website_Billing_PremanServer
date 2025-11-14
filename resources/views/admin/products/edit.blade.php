@extends('layouts.app-admin')

@section('title', 'Edit Product')

@section('content')
<div class="container mx-auto px-4 py-6 max-w-4xl">
    <div class="mb-6 flex items-center">
        <a href="{{ route('admin.products.index') }}" class="text-gray-600 hover:text-gray-900 mr-4">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
        </a>
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Edit Product</h1>
            <p class="text-gray-600 mt-1">Update product information</p>
        </div>
    </div>

    <form method="POST" action="{{ route('admin.products.update', $product) }}" class="bg-white rounded-lg shadow-md">
        @csrf
        @method('PUT')
        <div class="p-6 space-y-6">
            <div>
                <h2 class="text-lg font-semibold text-gray-900 mb-4 pb-2 border-b">Basic Information</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Product Name <span class="text-red-500">*</span></label>
                        <input type="text" name="name" value="{{ old('name', $product->name) }}" required class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                        <textarea name="description" rows="3" class="w-full px-4 py-2 border rounded-lg">{{ old('description', $product->description) }}</textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Product Group</label>
                        <select name="product_group_id" required class="w-full px-4 py-2 border rounded-lg">
                            @foreach($productGroups as $group)
                            <option value="{{ $group->id }}" {{ old('product_group_id', $product->product_group_id) == $group->id ? 'selected' : '' }}>{{ $group->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Type</label>
                        <select name="type" required class="w-full px-4 py-2 border rounded-lg">
                            <option value="hosting" {{ old('type', $product->type) == 'hosting' ? 'selected' : '' }}>Hosting</option>
                            <option value="vps" {{ old('type', $product->type) == 'vps' ? 'selected' : '' }}>VPS</option>
                            <option value="domain" {{ old('type', $product->type) == 'domain' ? 'selected' : '' }}>Domain</option>
                            <option value="license" {{ old('type', $product->type) == 'license' ? 'selected' : '' }}>License</option>
                            <option value="other" {{ old('type', $product->type) == 'other' ? 'selected' : '' }}>Other</option>
                        </select>
                    </div>
                </div>
            </div>

            <div>
                <h2 class="text-lg font-semibold text-gray-900 mb-4 pb-2 border-b">Pricing</h2>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Price</label>
                        <div class="relative">
                            <span class="absolute left-3 top-2 text-gray-500">$</span>
                            <input type="number" name="price" value="{{ old('price', $product->price) }}" step="0.01" required class="w-full pl-8 pr-4 py-2 border rounded-lg">
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Billing Cycle</label>
                        <select name="billing_cycle" required class="w-full px-4 py-2 border rounded-lg">
                            <option value="monthly" {{ $product->billing_cycle == 'monthly' ? 'selected' : '' }}>Monthly</option>
                            <option value="quarterly" {{ $product->billing_cycle == 'quarterly' ? 'selected' : '' }}>Quarterly</option>
                            <option value="annually" {{ $product->billing_cycle == 'annually' ? 'selected' : '' }}>Annually</option>
                            <option value="one-time" {{ $product->billing_cycle == 'one-time' ? 'selected' : '' }}>One-Time</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Setup Fee</label>
                        <div class="relative">
                            <span class="absolute left-3 top-2 text-gray-500">$</span>
                            <input type="number" name="setup_fee" value="{{ old('setup_fee', $product->setup_fee) }}" step="0.01" class="w-full pl-8 pr-4 py-2 border rounded-lg">
                        </div>
                    </div>
                </div>
            </div>

            <div>
                <label class="flex items-center">
                    <input type="checkbox" name="is_active" value="1" {{ $product->is_active ? 'checked' : '' }} class="h-4 w-4 text-blue-600 rounded">
                    <span class="ml-2 text-sm text-gray-700">Active</span>
                </label>
            </div>

            @if($product->services->count() > 0)
            <div class="bg-yellow-50 border border-yellow-200 rounded p-4">
                <p class="text-sm text-yellow-800"><strong>Note:</strong> {{ $product->services->count() }} active service(s). Price changes affect new orders only.</p>
            </div>
            @endif
        </div>

        <div class="bg-gray-50 px-6 py-4 rounded-b-lg flex justify-between">
            <a href="{{ route('admin.products.index') }}" class="text-gray-600 hover:text-gray-900 font-medium">Cancel</a>
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-8 py-2 rounded-lg font-semibold">Update</button>
        </div>
    </form>
</div>
@endsection
