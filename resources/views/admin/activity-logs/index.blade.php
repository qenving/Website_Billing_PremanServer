@extends('layouts.app-admin')

@section('title', 'Activity Logs')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Activity Logs</h1>
            <p class="text-gray-600 mt-1">System audit trail and user activity monitoring</p>
        </div>
    </div>

    @if(session('success'))
    <div class="mb-6 bg-green-50 border-l-4 border-green-500 p-4 rounded">
        <p class="text-green-700">{{ session('success') }}</p>
    </div>
    @endif

    <!-- Filters -->
    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <form method="GET" class="flex flex-wrap gap-4 items-end">
            <div class="flex-1 min-w-[200px]">
                <label class="block text-sm font-medium text-gray-700 mb-2">Activity Type</label>
                <select name="type" class="w-full px-4 py-2 border rounded-lg">
                    <option value="">All Types</option>
                    @foreach($types as $type)
                    <option value="{{ $type }}" {{ request('type') == $type ? 'selected' : '' }}>
                        {{ ucfirst($type) }}
                    </option>
                    @endforeach
                </select>
            </div>
            <div class="flex-1 min-w-[200px]">
                <label class="block text-sm font-medium text-gray-700 mb-2">Start Date</label>
                <input type="date" name="start_date" value="{{ request('start_date') }}" class="w-full px-4 py-2 border rounded-lg">
            </div>
            <div class="flex-1 min-w-[200px]">
                <label class="block text-sm font-medium text-gray-700 mb-2">End Date</label>
                <input type="date" name="end_date" value="{{ request('end_date') }}" class="w-full px-4 py-2 border rounded-lg">
            </div>
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg font-semibold">
                Filter
            </button>
            @if(request()->hasAny(['type', 'user_id', 'start_date', 'end_date']))
            <a href="{{ route('admin.activity-logs.index') }}" class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-6 py-2 rounded-lg font-semibold">
                Clear
            </a>
            @endif
        </form>
    </div>

    <!-- Activity Logs Table -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Time</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">IP Address</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($logs as $log)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $log->created_at->format('Y-m-d H:i:s') }}
                            <span class="block text-xs text-gray-500">{{ $log->created_at->diffForHumans() }}</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($log->user)
                            <div>
                                <p class="text-sm font-medium text-gray-900">{{ $log->user->name }}</p>
                                <p class="text-xs text-gray-500">{{ $log->user->email }}</p>
                            </div>
                            @else
                            <span class="text-sm text-gray-500">System</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @php
                            $typeColors = [
                                'login' => 'bg-green-100 text-green-800',
                                'logout' => 'bg-gray-100 text-gray-800',
                                'created' => 'bg-blue-100 text-blue-800',
                                'updated' => 'bg-yellow-100 text-yellow-800',
                                'deleted' => 'bg-red-100 text-red-800',
                                'failed_login' => 'bg-red-100 text-red-800',
                            ];
                            @endphp
                            <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $typeColors[$log->type] ?? 'bg-gray-100 text-gray-800' }}">
                                {{ ucfirst($log->type) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-900">
                            {{ $log->description }}
                            @if($log->subject_type)
                            <span class="block text-xs text-gray-500 mt-1">
                                {{ class_basename($log->subject_type) }} #{{ $log->subject_id }}
                            </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $log->ip_address ?? 'N/A' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                            <a href="{{ route('admin.activity-logs.show', $log) }}" class="text-blue-600 hover:text-blue-900">
                                View Details
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            <p class="mt-4 text-gray-500 font-medium">No activity logs found</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Pagination -->
    @if($logs->hasPages())
    <div class="mt-6">
        {{ $logs->links() }}
    </div>
    @endif

    <!-- Clear Logs -->
    <div class="mt-6 bg-yellow-50 border border-yellow-200 rounded-lg p-6">
        <h3 class="text-sm font-semibold text-yellow-900 mb-3">Clear Old Logs</h3>
        <form method="POST" action="{{ route('admin.activity-logs.clear') }}" class="flex items-end gap-4">
            @csrf
            <div>
                <label class="block text-sm font-medium text-yellow-900 mb-2">Delete logs older than</label>
                <div class="flex items-center gap-2">
                    <input type="number" name="days" value="90" min="1" class="px-4 py-2 border rounded-lg" required>
                    <span class="text-sm text-yellow-900">days</span>
                </div>
            </div>
            <button type="submit" class="bg-yellow-600 hover:bg-yellow-700 text-white px-6 py-2 rounded-lg font-semibold" onclick="return confirm('Are you sure you want to delete old logs?')">
                Clear Logs
            </button>
        </form>
    </div>
</div>
@endsection
