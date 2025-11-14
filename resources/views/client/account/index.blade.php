@extends('layouts.app-client')

@section('title', 'Account Settings')

@section('content')
<div class="container mx-auto px-4 py-8 max-w-4xl">
    <h1 class="text-3xl font-bold text-gray-900 mb-6">Account Settings</h1>

    @if(session('success'))
    <div class="mb-6 bg-green-50 border-l-4 border-green-500 p-4 rounded">
        <p class="text-green-700">{{ session('success') }}</p>
    </div>
    @endif

    <div class="space-y-6">
        <!-- Profile Information -->
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-xl font-semibold mb-4">Profile Information</h2>
            <form method="POST" action="{{ route('client.account.update-profile') }}">
                @csrf
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Full Name</label>
                        <input type="text" name="name" value="{{ old('name', auth()->user()->name) }}" required class="w-full px-4 py-2 border rounded-lg @error('name') border-red-500 @enderror">
                        @error('name')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Email Address</label>
                        <input type="email" name="email" value="{{ old('email', auth()->user()->email) }}" required class="w-full px-4 py-2 border rounded-lg @error('email') border-red-500 @enderror">
                        @error('email')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg font-semibold">Update Profile</button>
                </div>
            </form>
        </div>

        <!-- Change Password -->
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-xl font-semibold mb-4">Change Password</h2>
            <form method="POST" action="{{ route('client.account.update-password') }}">
                @csrf
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Current Password</label>
                        <input type="password" name="current_password" required class="w-full px-4 py-2 border rounded-lg">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">New Password</label>
                        <input type="password" name="password" required class="w-full px-4 py-2 border rounded-lg">
                        <p class="mt-1 text-xs text-gray-500">Minimum 8 characters</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Confirm New Password</label>
                        <input type="password" name="password_confirmation" required class="w-full px-4 py-2 border rounded-lg">
                    </div>
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg font-semibold">Change Password</button>
                </div>
            </form>
        </div>

        <!-- Two-Factor Authentication -->
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-xl font-semibold mb-4">Two-Factor Authentication</h2>
            @if(auth()->user()->two_factor_enabled)
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-green-600 font-medium">✓ Two-Factor Authentication is enabled</p>
                    <p class="text-sm text-gray-600 mt-1">Your account is protected with 2FA</p>
                </div>
                <form method="POST" action="{{ route('client.account.disable-2fa') }}">
                    @csrf
                    <button type="submit" class="bg-red-600 hover:bg-red-700 text-white px-6 py-2 rounded-lg font-semibold">Disable 2FA</button>
                </form>
            </div>
            @else
            <div class="flex items-center justify-between">
                <div>
                    <p class="font-medium">Two-Factor Authentication is disabled</p>
                    <p class="text-sm text-gray-600 mt-1">Add an extra layer of security to your account</p>
                </div>
                <form method="POST" action="{{ route('client.account.enable-2fa') }}">
                    @csrf
                    <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded-lg font-semibold">Enable 2FA</button>
                </form>
            </div>
            @endif
        </div>

        <!-- Account Information -->
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-xl font-semibold mb-4">Account Information</h2>
            <dl class="grid grid-cols-2 gap-4">
                <div>
                    <dt class="text-sm text-gray-500">Account ID</dt>
                    <dd class="font-medium mt-1">#{{ auth()->user()->id }}</dd>
                </div>
                <div>
                    <dt class="text-sm text-gray-500">Member Since</dt>
                    <dd class="font-medium mt-1">{{ auth()->user()->created_at->format('M d, Y') }}</dd>
                </div>
                <div>
                    <dt class="text-sm text-gray-500">Account Status</dt>
                    <dd class="mt-1">
                        @if(auth()->user()->is_active)
                        <span class="px-2 py-1 bg-green-100 text-green-800 rounded text-xs font-semibold">Active</span>
                        @else
                        <span class="px-2 py-1 bg-red-100 text-red-800 rounded text-xs font-semibold">Inactive</span>
                        @endif
                    </dd>
                </div>
                <div>
                    <dt class="text-sm text-gray-500">Email Verified</dt>
                    <dd class="mt-1">
                        @if(auth()->user()->email_verified_at)
                        <span class="text-green-600">✓ Verified</span>
                        @else
                        <span class="text-red-600">✗ Not Verified</span>
                        @endif
                    </dd>
                </div>
            </dl>
        </div>
    </div>
</div>
@endsection
