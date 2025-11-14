@extends('layouts.app-admin')

@section('title', 'Edit Invoice')

@section('content')
<div class="container mx-auto px-4 py-6 max-w-4xl">
    <div class="mb-6 flex items-center">
        <a href="{{ route('admin.invoices.show', $invoice) }}" class="text-gray-600 hover:text-gray-900 mr-4">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
        </a>
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Edit Invoice #{{ $invoice->invoice_number }}</h1>
        </div>
    </div>

    <form method="POST" action="{{ route('admin.invoices.update', $invoice) }}" class="bg-white rounded-lg shadow-md">
        @csrf
        @method('PUT')
        <div class="p-6 space-y-6">
            <div class="grid grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Client</label>
                    <select name="client_id" required class="w-full px-4 py-2 border rounded-lg">
                        @foreach($clients as $client)
                        <option value="{{ $client->id }}" {{ $invoice->client_id == $client->id ? 'selected' : '' }}>
                            {{ $client->user->name }}
                        </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Due Date</label>
                    <input type="date" name="due_date" value="{{ $invoice->due_date ? $invoice->due_date->format('Y-m-d') : '' }}" class="w-full px-4 py-2 border rounded-lg">
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                <select name="status" class="w-full px-4 py-2 border rounded-lg">
                    <option value="draft" {{ $invoice->status == 'draft' ? 'selected' : '' }}>Draft</option>
                    <option value="unpaid" {{ $invoice->status == 'unpaid' ? 'selected' : '' }}>Unpaid</option>
                    <option value="paid" {{ $invoice->status == 'paid' ? 'selected' : '' }}>Paid</option>
                    <option value="cancelled" {{ $invoice->status == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Notes</label>
                <textarea name="notes" rows="3" class="w-full px-4 py-2 border rounded-lg">{{ old('notes', $invoice->notes) }}</textarea>
            </div>

            @if($invoice->status != 'paid')
            <div class="bg-yellow-50 border border-yellow-200 rounded p-4">
                <p class="text-sm text-yellow-800"><strong>Note:</strong> To modify invoice items, please delete and recreate the invoice.</p>
            </div>
            @endif
        </div>

        <div class="bg-gray-50 px-6 py-4 rounded-b-lg flex justify-between">
            <a href="{{ route('admin.invoices.show', $invoice) }}" class="text-gray-600 hover:text-gray-900 font-medium">Cancel</a>
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-8 py-2 rounded-lg font-semibold">Update Invoice</button>
        </div>
    </form>
</div>
@endsection
