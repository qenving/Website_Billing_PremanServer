@extends('layouts.guest')

@section('title', '403 - Access Denied')

@section('content')
<div class="min-h-screen flex items-center justify-center bg-gray-100 px-4">
    <div class="max-w-md w-full text-center">
        <div class="mb-8">
            <h1 class="text-9xl font-bold text-yellow-600">403</h1>
            <h2 class="text-3xl font-bold text-gray-900 mt-4">Access Denied</h2>
            <p class="text-gray-600 mt-4">You don't have permission to access this resource.</p>
        </div>

        <div class="mb-8">
            <svg class="mx-auto h-48 w-48 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
            </svg>
        </div>

        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-6">
            <p class="text-sm text-yellow-800">
                <strong>Why am I seeing this?</strong><br>
                This page is restricted and requires special permissions to access.
            </p>
        </div>

        <div class="space-y-3">
            <a href="{{ url('/') }}" class="block w-full bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg font-semibold transition duration-200">
                Go to Homepage
            </a>
            <button onclick="history.back()" class="block w-full bg-gray-200 hover:bg-gray-300 text-gray-700 px-6 py-3 rounded-lg font-semibold transition duration-200">
                Go Back
            </button>
        </div>

        <div class="mt-8 text-sm text-gray-500">
            <p>Error Code: 403</p>
            <p class="mt-1">If you believe you should have access, please <a href="{{ route('client.tickets.create') }}" class="text-blue-600 hover:underline">contact support</a>.</p>
        </div>
    </div>
</div>
@endsection
