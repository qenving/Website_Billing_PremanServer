@extends('layouts.app-admin')

@section('title', 'View User')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="mb-6">
        <div class="flex items-center justify-between">
            <div class="flex items-center">
                <a href="{{ route('admin.users.index') }}" class="text-gray-600 hover:text-gray-900 mr-4">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                </a>
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">User Details</h1>
                    <p class="text-gray-600 mt-1">Complete user information and activity</p>
                </div>
            </div>
            <a href="{{ route('admin.users.edit', $user) }}" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg font-semibold transition duration-200">
                Edit User
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- User Profile Card -->
        <div class="lg:col-span-1">
            <div class="bg-white rounded-lg shadow-md p-6">
                <div class="text-center">
                    <div class="mx-auto w-24 h-24 rounded-full bg-blue-600 flex items-center justify-center text-white text-3xl font-bold mb-4">
                        {{ strtoupper(substr($user->name, 0, 2)) }}
                    </div>
                    <h2 class="text-2xl font-bold text-gray-900">{{ $user->name }}</h2>
                    <p class="text-gray-600 mt-1">{{ $user->email }}</p>
                    <div class="mt-4 flex justify-center space-x-2">
                        @if($user->is_active)
                        <span class="px-3 py-1 bg-green-100 text-green-800 rounded-full text-sm font-semibold">Active</span>
                        @else
                        <span class="px-3 py-1 bg-red-100 text-red-800 rounded-full text-sm font-semibold">Inactive</span>
                        @endif
                        @if($user->role)
                        <span class="px-3 py-1 bg-blue-100 text-blue-800 rounded-full text-sm font-semibold">
                            {{ ucwords(str_replace('_', ' ', $user->role->name)) }}
                        </span>
                        @endif
                    </div>
                </div>

                <div class="mt-6 pt-6 border-t border-gray-200">
                    <h3 class="font-semibold text-gray-900 mb-4">Quick Info</h3>
                    <dl class="space-y-3">
                        <div>
                            <dt class="text-sm text-gray-500">User ID</dt>
                            <dd class="text-sm font-medium text-gray-900">#{{ $user->id }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm text-gray-500">Joined Date</dt>
                            <dd class="text-sm font-medium text-gray-900">{{ $user->created_at->format('M d, Y') }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm text-gray-500">Last Updated</dt>
                            <dd class="text-sm font-medium text-gray-900">{{ $user->updated_at->diffForHumans() }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm text-gray-500">Email Verified</dt>
                            <dd class="text-sm font-medium">
                                @if($user->email_verified_at)
                                <span class="text-green-600">✓ Verified</span>
                                @else
                                <span class="text-red-600">✗ Not Verified</span>
                                @endif
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm text-gray-500">Two-Factor Auth</dt>
                            <dd class="text-sm font-medium">
                                @if($user->two_factor_enabled)
                                <span class="text-green-600">✓ Enabled</span>
                                @else
                                <span class="text-gray-600">Disabled</span>
                                @endif
                            </dd>
                        </div>
                    </dl>
                </div>

                <div class="mt-6 pt-6 border-t border-gray-200">
                    <h3 class="font-semibold text-gray-900 mb-4">Actions</h3>
                    <div class="space-y-2">
                        <form method="POST" action="{{ route('admin.users.toggle-status', $user) }}">
                            @csrf
                            <button type="submit" class="w-full text-left px-4 py-2 text-sm {{ $user->is_active ? 'text-red-700 hover:bg-red-50' : 'text-green-700 hover:bg-green-50' }} rounded-lg transition">
                                {{ $user->is_active ? 'Deactivate Account' : 'Activate Account' }}
                            </button>
                        </form>
                        @if($user->id != auth()->id())
                        <form method="POST" action="{{ route('admin.users.destroy', $user) }}" onsubmit="return confirm('Are you sure you want to delete this user?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="w-full text-left px-4 py-2 text-sm text-red-700 hover:bg-red-50 rounded-lg transition">
                                Delete User
                            </button>
                        </form>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- User Details & Activity -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Statistics Cards -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="bg-white rounded-lg shadow-md p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-gray-500 text-sm">Services</p>
                            <p class="text-2xl font-bold text-gray-900 mt-1">{{ $user->client ? $user->client->services->count() : 0 }}</p>
                        </div>
                        <div class="bg-blue-100 rounded-full p-3">
                            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2m-2-4h.01M17 16h.01"/>
                            </svg>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow-md p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-gray-500 text-sm">Invoices</p>
                            <p class="text-2xl font-bold text-gray-900 mt-1">{{ $user->client ? $user->client->invoices->count() : 0 }}</p>
                        </div>
                        <div class="bg-green-100 rounded-full p-3">
                            <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow-md p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-gray-500 text-sm">Tickets</p>
                            <p class="text-2xl font-bold text-gray-900 mt-1">{{ $user->tickets ? $user->tickets->count() : 0 }}</p>
                        </div>
                        <div class="bg-yellow-100 rounded-full p-3">
                            <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"/>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Role Permissions -->
            @if($user->role)
            <div class="bg-white rounded-lg shadow-md p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Role & Permissions</h3>
                <div class="bg-gray-50 rounded-lg p-4">
                    <div class="flex items-center mb-3">
                        <div class="bg-blue-100 rounded-full p-2 mr-3">
                            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                            </svg>
                        </div>
                        <div>
                            <p class="font-semibold text-gray-900">{{ ucwords(str_replace('_', ' ', $user->role->name)) }}</p>
                            <p class="text-sm text-gray-600">{{ $user->role->description ?? 'System role with specific permissions' }}</p>
                        </div>
                    </div>
                    @if($user->role->permissions)
                    <div class="mt-4">
                        <p class="text-sm font-medium text-gray-700 mb-2">Permissions:</p>
                        <div class="flex flex-wrap gap-2">
                            @foreach(json_decode($user->role->permissions, true) ?? [] as $permission)
                            <span class="px-2 py-1 bg-blue-50 text-blue-700 text-xs rounded">{{ $permission }}</span>
                            @endforeach
                        </div>
                    </div>
                    @endif
                </div>
            </div>
            @endif

            <!-- Recent Activity -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Recent Activity</h3>
                <div class="space-y-4">
                    @forelse($user->auditLogs ?? [] as $log)
                    <div class="flex items-start">
                        <div class="bg-gray-100 rounded-full p-2 mr-3">
                            <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <div class="flex-1">
                            <p class="text-sm text-gray-900">{{ $log->action }}</p>
                            <p class="text-xs text-gray-500 mt-1">{{ $log->created_at->diffForHumans() }}</p>
                        </div>
                    </div>
                    @empty
                    <div class="text-center py-8 text-gray-500">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                        </svg>
                        <p class="mt-2">No recent activity</p>
                    </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
