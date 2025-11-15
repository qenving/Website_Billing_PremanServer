@extends('layouts.app-admin')

@section('title', 'Create Invoice')

@section('content')
<div class="container mx-auto px-4 py-6 max-w-4xl">
    <div class="mb-6 flex items-center">
        <a href="{{ route('admin.invoices.index') }}" class="text-gray-600 hover:text-gray-900 mr-4">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
        </a>
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Create Invoice</h1>
            <p class="text-gray-600 mt-1">Generate a new invoice</p>
        </div>
    </div>

    <form method="POST" action="{{ route('admin.invoices.store') }}" class="bg-white rounded-lg shadow-md">
        @csrf
        <div class="p-6 space-y-6">
            <div class="grid grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Client <span class="text-red-500">*</span></label>
                    <select name="client_id" required class="w-full px-4 py-2 border rounded-lg">
                        <option value="">Select client...</option>
                        @foreach($clients as $client)
                        <option value="{{ $client->id }}">{{ $client->user->name }} ({{ $client->user->email }})</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Due Date</label>
                    <input type="date" name="due_date" value="{{ old('due_date', now()->addDays(7)->format('Y-m-d')) }}" class="w-full px-4 py-2 border rounded-lg">
                </div>
            </div>

            <div>
                <h3 class="text-lg font-semibold mb-4">Invoice Items</h3>
                <div id="invoice-items">
                    <div class="invoice-item grid grid-cols-12 gap-4 mb-4">
                        <div class="col-span-5">
                            <input type="text" name="items[0][description]" placeholder="Description" required class="w-full px-4 py-2 border rounded-lg">
                        </div>
                        <div class="col-span-2">
                            <input type="number" name="items[0][quantity]" value="1" min="1" placeholder="Qty" required class="w-full px-4 py-2 border rounded-lg">
                        </div>
                        <div class="col-span-3">
                            <input type="number" name="items[0][unit_price]" step="0.01" placeholder="Price" required class="w-full px-4 py-2 border rounded-lg">
                        </div>
                        <div class="col-span-2 flex items-center">
                            <button type="button" onclick="removeItem(this)" class="text-red-600 hover:text-red-900">Remove</button>
                        </div>
                    </div>
                </div>
                <button type="button" onclick="addItem()" class="text-blue-600 hover:text-blue-800 font-medium text-sm">+ Add Item</button>
            </div>

            <div class="grid grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Tax (%)</label>
                    <input type="number" name="tax_rate" value="0" step="0.01" min="0" class="w-full px-4 py-2 border rounded-lg">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                    <select name="status" class="w-full px-4 py-2 border rounded-lg">
                        <option value="draft">Draft</option>
                        <option value="unpaid" selected>Unpaid</option>
                        <option value="paid">Paid</option>
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Notes</label>
                <textarea name="notes" rows="3" class="w-full px-4 py-2 border rounded-lg" placeholder="Internal notes..."></textarea>
            </div>
        </div>

        <div class="bg-gray-50 px-6 py-4 rounded-b-lg flex justify-between">
            <a href="{{ route('admin.invoices.index') }}" class="text-gray-600 hover:text-gray-900 font-medium">Cancel</a>
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-8 py-2 rounded-lg font-semibold">Create Invoice</button>
        </div>
    </form>
</div>

<script>
let itemCount = 1;
function addItem() {
    const container = document.getElementById('invoice-items');
    const div = document.createElement('div');
    div.className = 'invoice-item grid grid-cols-12 gap-4 mb-4';
    div.innerHTML = `
        <div class="col-span-5">
            <input type="text" name="items[${itemCount}][description]" placeholder="Description" required class="w-full px-4 py-2 border rounded-lg">
        </div>
        <div class="col-span-2">
            <input type="number" name="items[${itemCount}][quantity]" value="1" min="1" placeholder="Qty" required class="w-full px-4 py-2 border rounded-lg">
        </div>
        <div class="col-span-3">
            <input type="number" name="items[${itemCount}][unit_price]" step="0.01" placeholder="Price" required class="w-full px-4 py-2 border rounded-lg">
        </div>
        <div class="col-span-2 flex items-center">
            <button type="button" onclick="removeItem(this)" class="text-red-600 hover:text-red-900">Remove</button>
        </div>
    `;
    container.appendChild(div);
    itemCount++;
}
function removeItem(btn) {
    if(document.querySelectorAll('.invoice-item').length > 1) {
        btn.closest('.invoice-item').remove();
    }
}
</script>
@endsection
