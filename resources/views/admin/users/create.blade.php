@extends('layouts.app-admin')

@section('title', 'Create New User')

@section('content')
<div class="container mx-auto px-4 py-6">
    <!-- Page Header -->
    <div class="mb-6">
        <div class="flex items-center mb-4">
            <a href="{{ route('admin.users.index') }}" class="text-gray-600 hover:text-gray-900 mr-4">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
            </a>
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Create New User</h1>
                <p class="text-gray-600 mt-1">Add a new user to the system</p>
            </div>
        </div>
    </div>

    <div class="max-w-4xl">
        <form method="POST" action="{{ route('admin.users.store') }}" class="bg-white rounded-lg shadow-md">
            @csrf

            <div class="p-6 space-y-6">
                <!-- Personal Information Section -->
                <div>
                    <h2 class="text-lg font-semibold text-gray-900 mb-4 pb-2 border-b">Personal Information</h2>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Full Name -->
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                                Full Name <span class="text-red-500">*</span>
                            </label>
                            <input type="text"
                                   name="name"
                                   id="name"
                                   value="{{ old('name') }}"
                                   required
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('name') border-red-500 @enderror">
                            @error('name')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Email -->
                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                                Email Address <span class="text-red-500">*</span>
                            </label>
                            <input type="email"
                                   name="email"
                                   id="email"
                                   value="{{ old('email') }}"
                                   required
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('email') border-red-500 @enderror">
                            @error('email')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Security Section -->
                <div>
                    <h2 class="text-lg font-semibold text-gray-900 mb-4 pb-2 border-b">Security</h2>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Password -->
                        <div>
                            <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
                                Password <span class="text-red-500">*</span>
                            </label>
                            <input type="password"
                                   name="password"
                                   id="password"
                                   required
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('password') border-red-500 @enderror">
                            <p class="mt-1 text-xs text-gray-500">Minimum 8 characters</p>
                            @error('password')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Confirm Password -->
                        <div>
                            <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-2">
                                Confirm Password <span class="text-red-500">*</span>
                            </label>
                            <input type="password"
                                   name="password_confirmation"
                                   id="password_confirmation"
                                   required
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        </div>
                    </div>
                </div>

                <!-- Role & Permissions Section -->
                <div>
                    <h2 class="text-lg font-semibold text-gray-900 mb-4 pb-2 border-b">Role & Permissions</h2>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Role -->
                        <div>
                            <label for="role_id" class="block text-sm font-medium text-gray-700 mb-2">
                                User Role <span class="text-red-500">*</span>
                            </label>
                            <select name="role_id"
                                    id="role_id"
                                    required
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('role_id') border-red-500 @enderror">
                                <option value="">Select a role...</option>
                                @foreach($roles as $role)
                                <option value="{{ $role->id }}" {{ old('role_id') == $role->id ? 'selected' : '' }}>
                                    {{ ucwords(str_replace('_', ' ', $role->name)) }}
                                </option>
                                @endforeach
                            </select>
                            @error('role_id')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                            <p class="mt-1 text-xs text-gray-500">Defines what this user can access</p>
                        </div>

                        <!-- Status -->
                        <div>
                            <label for="is_active" class="block text-sm font-medium text-gray-700 mb-2">
                                Account Status
                            </label>
                            <select name="is_active"
                                    id="is_active"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                <option value="1" {{ old('is_active', '1') == '1' ? 'selected' : '' }}>Active</option>
                                <option value="0" {{ old('is_active') == '0' ? 'selected' : '' }}>Inactive</option>
                            </select>
                            <p class="mt-1 text-xs text-gray-500">Inactive users cannot login</p>
                        </div>
                    </div>
                </div>

                <!-- Additional Settings -->
                <div>
                    <h2 class="text-lg font-semibold text-gray-900 mb-4 pb-2 border-b">Additional Settings</h2>

                    <div class="space-y-4">
                        <!-- Email Verification -->
                        <div class="flex items-center">
                            <input type="checkbox"
                                   name="email_verified"
                                   id="email_verified"
                                   value="1"
                                   {{ old('email_verified', '1') ? 'checked' : '' }}
                                   class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                            <label for="email_verified" class="ml-2 block text-sm text-gray-700">
                                Mark email as verified
                            </label>
                        </div>

                        <!-- Send Welcome Email -->
                        <div class="flex items-center">
                            <input type="checkbox"
                                   name="send_welcome_email"
                                   id="send_welcome_email"
                                   value="1"
                                   {{ old('send_welcome_email', '1') ? 'checked' : '' }}
                                   class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                            <label for="send_welcome_email" class="ml-2 block text-sm text-gray-700">
                                Send welcome email with login credentials
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="bg-gray-50 px-6 py-4 rounded-b-lg flex justify-between items-center">
                <a href="{{ route('admin.users.index') }}" class="text-gray-600 hover:text-gray-900 font-medium">
                    Cancel
                </a>
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-8 py-2 rounded-lg font-semibold transition duration-200">
                    Create User
                </button>
            </div>
        </form>

        <!-- Help Card -->
        <div class="mt-6 bg-blue-50 border border-blue-200 rounded-lg p-6">
            <div class="flex">
                <svg class="h-6 w-6 text-blue-600 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-blue-900">Tips for Creating Users</h3>
                    <div class="mt-2 text-sm text-blue-700 space-y-1">
                        <p>• <strong>Super Admin:</strong> Full system access, can manage everything</p>
                        <p>• <strong>Admin:</strong> Can manage users, products, services, and invoices</p>
                        <p>• <strong>Client:</strong> Can only access client portal features</p>
                        <p>• Strong passwords are recommended for admin accounts</p>
                        <p>• Welcome emails will contain login credentials</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
