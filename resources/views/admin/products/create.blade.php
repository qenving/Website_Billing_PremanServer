@extends('layouts.app-admin')

@section('title', 'Create Product')

@section('content')
<div class="container mx-auto px-4 py-6 max-w-4xl">
    <div class="mb-6 flex items-center">
        <a href="{{ route('admin.products.index') }}" class="text-gray-600 hover:text-gray-900 mr-4">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
        </a>
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Create Product</h1>
            <p class="text-gray-600 mt-1">Add a new product or service</p>
        </div>
    </div>

    <form method="POST" action="{{ route('admin.products.store') }}" class="bg-white rounded-lg shadow-md">
        @csrf
        <div class="p-6 space-y-6">
            <div>
                <h2 class="text-lg font-semibold text-gray-900 mb-4 pb-2 border-b">Basic Information</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Product Name <span class="text-red-500">*</span></label>
                        <input type="text" name="name" value="{{ old('name') }}" required class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 @error('name') border-red-500 @enderror">
                        @error('name')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                        <textarea name="description" rows="3" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500">{{ old('description') }}</textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Product Group <span class="text-red-500">*</span></label>
                        <select name="product_group_id" required class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 @error('product_group_id') border-red-500 @enderror">
                            <option value="">Select a group...</option>
                            @foreach($productGroups as $group)
                            <option value="{{ $group->id }}" {{ old('product_group_id') == $group->id ? 'selected' : '' }}>{{ $group->name }}</option>
                            @endforeach
                        </select>
                        @error('product_group_id')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Product Type <span class="text-red-500">*</span></label>
                        <select name="type" required class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500">
                            <option value="hosting" {{ old('type') == 'hosting' ? 'selected' : '' }}>Hosting</option>
                            <option value="vps" {{ old('type') == 'vps' ? 'selected' : '' }}>VPS</option>
                            <option value="domain" {{ old('type') == 'domain' ? 'selected' : '' }}>Domain</option>
                            <option value="license" {{ old('type') == 'license' ? 'selected' : '' }}>License</option>
                            <option value="other" {{ old('type') == 'other' ? 'selected' : '' }}>Other</option>
                        </select>
                    </div>
                </div>
            </div>

            <div>
                <h2 class="text-lg font-semibold text-gray-900 mb-4 pb-2 border-b">Pricing</h2>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Price <span class="text-red-500">*</span></label>
                        <div class="relative">
                            <span class="absolute left-3 top-2 text-gray-500">$</span>
                            <input type="number" name="price" value="{{ old('price') }}" step="0.01" required class="w-full pl-8 pr-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500">
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Billing Cycle <span class="text-red-500">*</span></label>
                        <select name="billing_cycle" required class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500">
                            <option value="monthly">Monthly</option>
                            <option value="quarterly">Quarterly</option>
                            <option value="semi-annually">Semi-Annually</option>
                            <option value="annually">Annually</option>
                            <option value="biennially">Biennially</option>
                            <option value="triennially">Triennially</option>
                            <option value="one-time">One-Time</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Setup Fee</label>
                        <div class="relative">
                            <span class="absolute left-3 top-2 text-gray-500">$</span>
                            <input type="number" name="setup_fee" value="{{ old('setup_fee', 0) }}" step="0.01" class="w-full pl-8 pr-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500">
                        </div>
                    </div>
                </div>
            </div>

            <div>
                <h2 class="text-lg font-semibold text-gray-900 mb-4 pb-2 border-b">Provisioning</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Provisioning Module</label>
                        <select name="provisioning_module" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500">
                            <option value="">None (Manual)</option>
                            @foreach($provisioningModules ?? [] as $module)
                            <option value="{{ $module->id }}">{{ $module->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Auto-provision</label>
                        <select name="auto_provision" class="w-full px-4 py-2 border rounded-lg">
                            <option value="0">Manual Provisioning</option>
                            <option value="1">Auto-provision on Payment</option>
                        </select>
                    </div>
                </div>
            </div>

            <div>
                <h2 class="text-lg font-semibold text-gray-900 mb-4 pb-2 border-b">Settings</h2>
                <div class="space-y-4">
                    <div class="flex items-center">
                        <input type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', '1') ? 'checked' : '' }} class="h-4 w-4 text-blue-600 rounded">
                        <label for="is_active" class="ml-2 text-sm text-gray-700">Active (visible to clients)</label>
                    </div>
                    <div class="flex items-center">
                        <input type="checkbox" name="allow_quantity" id="allow_quantity" value="1" {{ old('allow_quantity') ? 'checked' : '' }} class="h-4 w-4 text-blue-600 rounded">
                        <label for="allow_quantity" class="ml-2 text-sm text-gray-700">Allow clients to order multiple quantities</label>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-gray-50 px-6 py-4 rounded-b-lg flex justify-between">
            <a href="{{ route('admin.products.index') }}" class="text-gray-600 hover:text-gray-900 font-medium">Cancel</a>
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-8 py-2 rounded-lg font-semibold">Create Product</button>
        </div>
    </form>
</div>
@endsection
