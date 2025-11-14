@extends('layouts.app-admin')

@section('title', 'Edit Service')

@section('content')
<div class="container mx-auto px-4 py-6 max-w-3xl">
    <div class="mb-6 flex items-center">
        <a href="{{ route('admin.services.show', $service) }}" class="text-gray-600 hover:text-gray-900 mr-4">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
        </a>
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Edit Service #{{ $service->id }}</h1>
        </div>
    </div>

    <form method="POST" action="{{ route('admin.services.update', $service) }}" class="bg-white rounded-lg shadow-md">
        @csrf
        @method('PUT')
        <div class="p-6 space-y-6">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Domain</label>
                <input type="text" name="domain" value="{{ old('domain', $service->domain) }}" class="w-full px-4 py-2 border rounded-lg">
            </div>

            <div class="grid grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Price</label>
                    <div class="relative">
                        <span class="absolute left-3 top-2 text-gray-500">$</span>
                        <input type="number" name="price" value="{{ old('price', $service->price) }}" step="0.01" required class="w-full pl-8 pr-4 py-2 border rounded-lg">
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Billing Cycle</label>
                    <select name="billing_cycle" required class="w-full px-4 py-2 border rounded-lg">
                        <option value="monthly" {{ $service->billing_cycle == 'monthly' ? 'selected' : '' }}>Monthly</option>
                        <option value="quarterly" {{ $service->billing_cycle == 'quarterly' ? 'selected' : '' }}>Quarterly</option>
                        <option value="annually" {{ $service->billing_cycle == 'annually' ? 'selected' : '' }}>Annually</option>
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Next Due Date</label>
                <input type="date" name="next_due_date" value="{{ old('next_due_date', $service->next_due_date ? $service->next_due_date->format('Y-m-d') : '') }}" class="w-full px-4 py-2 border rounded-lg">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                <select name="status" required class="w-full px-4 py-2 border rounded-lg">
                    <option value="pending" {{ $service->status == 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="active" {{ $service->status == 'active' ? 'selected' : '' }}>Active</option>
                    <option value="suspended" {{ $service->status == 'suspended' ? 'selected' : '' }}>Suspended</option>
                    <option value="terminated" {{ $service->status == 'terminated' ? 'selected' : '' }}>Terminated</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Admin Notes</label>
                <textarea name="notes" rows="4" class="w-full px-4 py-2 border rounded-lg">{{ old('notes', $service->notes) }}</textarea>
                <p class="mt-1 text-xs text-gray-500">Internal notes (not visible to client)</p>
            </div>
        </div>

        <div class="bg-gray-50 px-6 py-4 rounded-b-lg flex justify-between">
            <a href="{{ route('admin.services.show', $service) }}" class="text-gray-600 hover:text-gray-900 font-medium">Cancel</a>
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-8 py-2 rounded-lg font-semibold">Update Service</button>
        </div>
    </form>
</div>
@endsection
