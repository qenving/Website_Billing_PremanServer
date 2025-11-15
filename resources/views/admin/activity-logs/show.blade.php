@extends('layouts.app-admin')

@section('title', 'Activity Log Details')

@section('content')
<div class="container mx-auto px-4 py-8 max-w-5xl">
    <div class="mb-6">
        <div class="flex items-center mb-4">
            <a href="{{ route('admin.activity-logs.index') }}" class="text-gray-600 hover:text-gray-900 mr-4">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
            </a>
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Activity Log Details</h1>
                <p class="text-gray-600 mt-1">Detailed information about this activity</p>
            </div>
        </div>
    </div>

    <!-- Log Overview -->
    <div class="bg-white rounded-lg shadow mb-6">
        <div class="p-6 border-b">
            <h2 class="text-lg font-semibold text-gray-900">Activity Overview</h2>
        </div>
        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Activity Type -->
                <div>
                    <label class="block text-sm font-medium text-gray-500 mb-2">Activity Type</label>
                    @php
                    $typeColors = [
                        'login' => 'bg-green-100 text-green-800',
                        'logout' => 'bg-gray-100 text-gray-800',
                        'created' => 'bg-blue-100 text-blue-800',
                        'updated' => 'bg-yellow-100 text-yellow-800',
                        'deleted' => 'bg-red-100 text-red-800',
                        'failed_login' => 'bg-red-100 text-red-800',
                        'payment_received' => 'bg-green-100 text-green-800',
                        'service_provisioned' => 'bg-blue-100 text-blue-800',
                        'user_registered' => 'bg-purple-100 text-purple-800',
                    ];
                    @endphp
                    <span class="inline-block px-4 py-2 text-sm font-semibold rounded-lg {{ $typeColors[$activityLog->type] ?? 'bg-gray-100 text-gray-800' }}">
                        {{ ucfirst(str_replace('_', ' ', $activityLog->type)) }}
                    </span>
                </div>

                <!-- User -->
                <div>
                    <label class="block text-sm font-medium text-gray-500 mb-2">Performed By</label>
                    @if($activityLog->user)
                    <div class="flex items-center">
                        <div class="w-10 h-10 rounded-full bg-blue-600 flex items-center justify-center text-white font-bold mr-3">
                            {{ strtoupper(substr($activityLog->user->name, 0, 2)) }}
                        </div>
                        <div>
                            <p class="font-semibold text-gray-900">{{ $activityLog->user->name }}</p>
                            <p class="text-sm text-gray-600">{{ $activityLog->user->email }}</p>
                        </div>
                    </div>
                    @else
                    <p class="text-gray-500">System</p>
                    @endif
                </div>

                <!-- Timestamp -->
                <div>
                    <label class="block text-sm font-medium text-gray-500 mb-2">Date & Time</label>
                    <p class="text-gray-900 font-medium">{{ $activityLog->created_at->format('F d, Y H:i:s') }}</p>
                    <p class="text-sm text-gray-600">{{ $activityLog->created_at->diffForHumans() }}</p>
                </div>

                <!-- IP Address -->
                <div>
                    <label class="block text-sm font-medium text-gray-500 mb-2">IP Address</label>
                    <p class="text-gray-900 font-mono">{{ $activityLog->ip_address ?? 'N/A' }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Description -->
    <div class="bg-white rounded-lg shadow mb-6">
        <div class="p-6 border-b">
            <h2 class="text-lg font-semibold text-gray-900">Description</h2>
        </div>
        <div class="p-6">
            <p class="text-gray-900 text-lg">{{ $activityLog->description }}</p>
        </div>
    </div>

    <!-- Subject Information -->
    @if($activityLog->subject_type && $activityLog->subject)
    <div class="bg-white rounded-lg shadow mb-6">
        <div class="p-6 border-b">
            <h2 class="text-lg font-semibold text-gray-900">Related Subject</h2>
        </div>
        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-500 mb-2">Subject Type</label>
                    <p class="text-gray-900 font-semibold">{{ class_basename($activityLog->subject_type) }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-500 mb-2">Subject ID</label>
                    <p class="text-gray-900 font-mono">#{{ $activityLog->subject_id }}</p>
                </div>
            </div>

            @if($activityLog->subject)
            <div class="mt-4 p-4 bg-gray-50 rounded-lg">
                <h3 class="text-sm font-semibold text-gray-900 mb-2">Subject Details:</h3>
                <div class="space-y-1 text-sm">
                    @if(method_exists($activityLog->subject, 'toArray'))
                        @foreach($activityLog->subject->toArray() as $key => $value)
                            @if(!in_array($key, ['password', 'remember_token', 'created_at', 'updated_at']) && !is_array($value) && !is_object($value))
                            <div class="flex">
                                <span class="text-gray-600 w-32">{{ ucfirst(str_replace('_', ' ', $key)) }}:</span>
                                <span class="text-gray-900 font-medium">{{ $value }}</span>
                            </div>
                            @endif
                        @endforeach
                    @endif
                </div>
            </div>
            @endif
        </div>
    </div>
    @endif

    <!-- Properties/Details -->
    @if($activityLog->properties && count($activityLog->properties) > 0)
    <div class="bg-white rounded-lg shadow mb-6">
        <div class="p-6 border-b">
            <h2 class="text-lg font-semibold text-gray-900">Additional Details</h2>
        </div>
        <div class="p-6">
            @if(isset($activityLog->properties['old']) && isset($activityLog->properties['new']))
            <!-- Show changes (for updated activities) -->
            <div class="space-y-4">
                <h3 class="text-sm font-semibold text-gray-900 mb-3">Changes Made:</h3>

                @foreach($activityLog->properties['new'] as $key => $newValue)
                    @php
                    $oldValue = $activityLog->properties['old'][$key] ?? null;
                    $hasChanged = $oldValue != $newValue;
                    @endphp

                    @if($hasChanged && !in_array($key, ['password', 'remember_token', 'updated_at']))
                    <div class="border-l-4 border-yellow-500 pl-4 py-2 bg-yellow-50">
                        <p class="text-sm font-semibold text-gray-900">{{ ucfirst(str_replace('_', ' ', $key)) }}</p>
                        <div class="mt-2 space-y-1">
                            <div class="flex items-center text-sm">
                                <span class="text-red-600 font-medium mr-2">Old:</span>
                                <span class="text-gray-700 line-through">{{ is_bool($oldValue) ? ($oldValue ? 'true' : 'false') : ($oldValue ?? 'N/A') }}</span>
                            </div>
                            <div class="flex items-center text-sm">
                                <span class="text-green-600 font-medium mr-2">New:</span>
                                <span class="text-gray-900 font-semibold">{{ is_bool($newValue) ? ($newValue ? 'true' : 'false') : $newValue }}</span>
                            </div>
                        </div>
                    </div>
                    @endif
                @endforeach
            </div>
            @else
            <!-- Show raw properties -->
            <div class="bg-gray-50 rounded-lg p-4">
                <pre class="text-xs font-mono text-gray-800 overflow-x-auto">{{ json_encode($activityLog->properties, JSON_PRETTY_PRINT) }}</pre>
            </div>
            @endif
        </div>
    </div>
    @endif

    <!-- Technical Details -->
    <div class="bg-white rounded-lg shadow">
        <div class="p-6 border-b">
            <h2 class="text-lg font-semibold text-gray-900">Technical Information</h2>
        </div>
        <div class="p-6">
            <div class="space-y-4">
                <!-- User Agent -->
                <div>
                    <label class="block text-sm font-medium text-gray-500 mb-2">User Agent</label>
                    <p class="text-sm text-gray-900 font-mono bg-gray-50 p-3 rounded">{{ $activityLog->user_agent ?? 'N/A' }}</p>
                </div>

                <!-- Log ID -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-2">Log ID</label>
                        <p class="text-gray-900 font-mono">#{{ $activityLog->id }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-2">Created At</label>
                        <p class="text-gray-900 text-sm">{{ $activityLog->created_at->toDateTimeString() }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-2">Updated At</label>
                        <p class="text-gray-900 text-sm">{{ $activityLog->updated_at->toDateTimeString() }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Back Button -->
    <div class="mt-6">
        <a href="{{ route('admin.activity-logs.index') }}" class="inline-flex items-center text-blue-600 hover:text-blue-700 font-medium">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Back to Activity Logs
        </a>
    </div>
</div>
@endsection
