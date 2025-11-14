@extends('layouts.app-client')

@section('title', 'Create Support Ticket')

@section('content')
<div class="container mx-auto px-4 py-8 max-w-3xl">
    <div class="mb-6">
        <div class="flex items-center mb-4">
            <a href="{{ route('client.tickets.index') }}" class="text-gray-600 hover:text-gray-900 mr-4">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
            </a>
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Create Support Ticket</h1>
                <p class="text-gray-600 mt-1">Get help from our support team</p>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow">
        <form method="POST" action="{{ route('client.tickets.store') }}" enctype="multipart/form-data">
            @csrf

            <div class="p-6 space-y-6">
                <!-- Subject -->
                <div>
                    <label for="subject" class="block text-sm font-medium text-gray-700 mb-2">
                        Subject <span class="text-red-500">*</span>
                    </label>
                    <input type="text"
                           name="subject"
                           id="subject"
                           value="{{ old('subject') }}"
                           required
                           class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('subject') border-red-500 @enderror"
                           placeholder="Brief description of your issue">
                    @error('subject')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>

                <!-- Department -->
                <div>
                    <label for="department_id" class="block text-sm font-medium text-gray-700 mb-2">
                        Department <span class="text-red-500">*</span>
                    </label>
                    <select name="department_id"
                            id="department_id"
                            required
                            class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('department_id') border-red-500 @enderror">
                        <option value="">Select a department...</option>
                        @foreach($departments ?? [] as $department)
                        <option value="{{ $department->id }}" {{ old('department_id') == $department->id ? 'selected' : '' }}>
                            {{ $department->name }}
                        </option>
                        @endforeach
                    </select>
                    @error('department_id')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    <p class="mt-1 text-xs text-gray-500">Choose the department that best matches your issue</p>
                </div>

                <!-- Priority -->
                <div>
                    <label for="priority" class="block text-sm font-medium text-gray-700 mb-2">
                        Priority <span class="text-red-500">*</span>
                    </label>
                    <select name="priority"
                            id="priority"
                            required
                            class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('priority') border-red-500 @enderror">
                        <option value="low" {{ old('priority', 'medium') == 'low' ? 'selected' : '' }}>Low - General inquiry</option>
                        <option value="medium" {{ old('priority', 'medium') == 'medium' ? 'selected' : '' }}>Medium - Normal issue</option>
                        <option value="high" {{ old('priority') == 'high' ? 'selected' : '' }}>High - Service affected</option>
                        <option value="urgent" {{ old('priority') == 'urgent' ? 'selected' : '' }}>Urgent - Service down</option>
                    </select>
                    @error('priority')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>

                <!-- Related Service (Optional) -->
                <div>
                    <label for="service_id" class="block text-sm font-medium text-gray-700 mb-2">
                        Related Service (Optional)
                    </label>
                    <select name="service_id"
                            id="service_id"
                            class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option value="">Not related to any service</option>
                        @foreach($services ?? [] as $service)
                        <option value="{{ $service->id }}" {{ old('service_id') == $service->id ? 'selected' : '' }}>
                            {{ $service->product->name ?? 'Service' }} - {{ $service->domain ?? $service->username ?? '#'.$service->id }}
                        </option>
                        @endforeach
                    </select>
                    <p class="mt-1 text-xs text-gray-500">Link this ticket to a specific service if applicable</p>
                </div>

                <!-- Message -->
                <div>
                    <label for="message" class="block text-sm font-medium text-gray-700 mb-2">
                        Message <span class="text-red-500">*</span>
                    </label>
                    <textarea name="message"
                              id="message"
                              rows="8"
                              required
                              class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('message') border-red-500 @enderror"
                              placeholder="Please describe your issue in detail...">{{ old('message') }}</textarea>
                    @error('message')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    <p class="mt-1 text-xs text-gray-500">Minimum 20 characters. Be as detailed as possible to help us assist you better.</p>
                </div>

                <!-- Attachments -->
                <div>
                    <label for="attachments" class="block text-sm font-medium text-gray-700 mb-2">
                        Attachments (Optional)
                    </label>
                    <input type="file"
                           name="attachments[]"
                           id="attachments"
                           multiple
                           accept="image/*,.pdf,.doc,.docx,.txt,.zip"
                           class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <p class="mt-1 text-xs text-gray-500">You can upload screenshots, logs, or documents (max 5MB each, up to 5 files)</p>
                </div>

                <!-- Important Notice -->
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                    <div class="flex">
                        <svg class="h-5 w-5 text-blue-600 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-blue-900">Tips for faster resolution:</h3>
                            <div class="mt-2 text-sm text-blue-700 space-y-1">
                                <p>• Provide detailed information about the issue</p>
                                <p>• Include error messages if any</p>
                                <p>• Mention steps to reproduce the problem</p>
                                <p>• Attach relevant screenshots</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="bg-gray-50 px-6 py-4 rounded-b-lg flex justify-between items-center">
                <a href="{{ route('client.tickets.index') }}" class="text-gray-600 hover:text-gray-900 font-medium">
                    Cancel
                </a>
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-8 py-2 rounded-lg font-semibold transition duration-200">
                    Submit Ticket
                </button>
            </div>
        </form>
    </div>

    <!-- FAQ Suggestion (Optional) -->
    <div class="mt-6 bg-gray-50 border border-gray-200 rounded-lg p-6">
        <h3 class="text-sm font-medium text-gray-900 mb-3">Before submitting a ticket...</h3>
        <p class="text-sm text-gray-600 mb-3">Check if your question is already answered in our Knowledge Base:</p>
        <div class="space-y-2">
            <a href="#" class="block text-sm text-blue-600 hover:underline">→ Getting Started Guide</a>
            <a href="#" class="block text-sm text-blue-600 hover:underline">→ Billing & Payment FAQ</a>
            <a href="#" class="block text-sm text-blue-600 hover:underline">→ Service Configuration Help</a>
            <a href="#" class="block text-sm text-blue-600 hover:underline">→ Common Issues & Solutions</a>
        </div>
    </div>
</div>
@endsection
