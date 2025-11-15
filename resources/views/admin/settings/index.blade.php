@extends('layouts.app-admin')

@section('title', 'System Settings')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">System Settings</h1>
            <p class="text-gray-600 mt-1">Configure your billing system</p>
        </div>
        <form method="POST" action="{{ route('admin.settings.clear-cache') }}">
            @csrf
            <button type="submit" class="bg-gray-600 hover:bg-gray-700 text-white px-6 py-2 rounded-lg font-semibold transition duration-200">
                Clear Cache
            </button>
        </form>
    </div>

    @if(session('success'))
    <div class="mb-6 bg-green-50 border-l-4 border-green-500 p-4 rounded">
        <p class="text-green-700">{{ session('success') }}</p>
    </div>
    @endif

    @if($errors->any())
    <div class="mb-6 bg-red-50 border-l-4 border-red-500 p-4 rounded">
        <div class="flex">
            <div class="ml-3">
                <h3 class="text-sm font-medium text-red-800">Please fix the following errors:</h3>
                <ul class="mt-2 text-sm text-red-700 list-disc list-inside">
                    @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
    @endif

    <form method="POST" action="{{ route('admin.settings.update') }}">
        @csrf
        @method('PUT')

        <!-- Tabs -->
        <div class="mb-6">
            <div class="border-b border-gray-200">
                <nav class="-mb-px flex space-x-8">
                    <button type="button" class="tab-button active border-b-2 border-blue-500 py-4 px-1 text-sm font-medium text-blue-600" data-tab="general">
                        General
                    </button>
                    <button type="button" class="tab-button border-b-2 border-transparent py-4 px-1 text-sm font-medium text-gray-500 hover:text-gray-700 hover:border-gray-300" data-tab="billing">
                        Billing
                    </button>
                    <button type="button" class="tab-button border-b-2 border-transparent py-4 px-1 text-sm font-medium text-gray-500 hover:text-gray-700 hover:border-gray-300" data-tab="email">
                        Email
                    </button>
                    <button type="button" class="tab-button border-b-2 border-transparent py-4 px-1 text-sm font-medium text-gray-500 hover:text-gray-700 hover:border-gray-300" data-tab="system">
                        System
                    </button>
                </nav>
            </div>
        </div>

        <!-- Tab Content -->
        @foreach($groups as $group)
        <div class="tab-content {{ $group === 'general' ? 'active' : 'hidden' }}" data-tab="{{ $group }}">
            <div class="bg-white rounded-lg shadow">
                <div class="p-6 border-b">
                    <h2 class="text-lg font-semibold text-gray-900">{{ ucfirst($group) }} Settings</h2>
                </div>
                <div class="p-6 space-y-6">
                    @forelse($settings[$group] ?? [] as $setting)
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            {{ $setting->label }}
                            @if($setting->description)
                            <span class="block text-xs text-gray-500 font-normal mt-1">{{ $setting->description }}</span>
                            @endif
                        </label>

                        @if($setting->type === 'boolean')
                        <div class="flex items-center">
                            <input type="hidden" name="settings[{{ $setting->key }}]" value="0">
                            <input type="checkbox" 
                                   name="settings[{{ $setting->key }}]" 
                                   value="1"
                                   {{ $setting->value ? 'checked' : '' }}
                                   class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                            <span class="ml-2 text-sm text-gray-600">Enable</span>
                        </div>
                        @elseif($setting->type === 'number')
                        <input type="number" 
                               name="settings[{{ $setting->key }}]" 
                               value="{{ $setting->value }}"
                               class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        @elseif($setting->type === 'textarea')
                        <textarea name="settings[{{ $setting->key }}]" 
                                  rows="4"
                                  class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">{{ $setting->value }}</textarea>
                        @else
                        <input type="text" 
                               name="settings[{{ $setting->key }}]" 
                               value="{{ $setting->value }}"
                               class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        @endif
                    </div>
                    @empty
                    <p class="text-gray-500">No settings available in this group</p>
                    @endforelse
                </div>
            </div>
        </div>
        @endforeach

        <!-- Save Button -->
        <div class="mt-6 flex justify-end">
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-8 py-3 rounded-lg font-semibold transition duration-200">
                Save Settings
            </button>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const tabButtons = document.querySelectorAll('.tab-button');
    const tabContents = document.querySelectorAll('.tab-content');

    tabButtons.forEach(button => {
        button.addEventListener('click', function() {
            const tab = this.dataset.tab;

            // Update buttons
            tabButtons.forEach(btn => {
                btn.classList.remove('active', 'border-blue-500', 'text-blue-600');
                btn.classList.add('border-transparent', 'text-gray-500');
            });
            this.classList.add('active', 'border-blue-500', 'text-blue-600');
            this.classList.remove('border-transparent', 'text-gray-500');

            // Update content
            tabContents.forEach(content => {
                if (content.dataset.tab === tab) {
                    content.classList.remove('hidden');
                    content.classList.add('active');
                } else {
                    content.classList.add('hidden');
                    content.classList.remove('active');
                }
            });
        });
    });
});
</script>
@endsection
