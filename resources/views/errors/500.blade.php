@extends('layouts.guest')

@section('title', '500 - Server Error')

@section('content')
<div class="min-h-screen flex items-center justify-center bg-gray-100 px-4">
    <div class="max-w-md w-full text-center">
        <div class="mb-8">
            <h1 class="text-9xl font-bold text-red-600">500</h1>
            <h2 class="text-3xl font-bold text-gray-900 mt-4">Server Error</h2>
            <p class="text-gray-600 mt-4">Oops! Something went wrong on our end.</p>
        </div>

        <div class="mb-8">
            <svg class="mx-auto h-48 w-48 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
        </div>

        <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6">
            <p class="text-sm text-red-800">
                <strong>What happened?</strong><br>
                Our server encountered an internal error and was unable to complete your request.
            </p>
        </div>

        <div class="space-y-3">
            <a href="{{ url('/') }}" class="block w-full bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg font-semibold transition duration-200">
                Go to Homepage
            </a>
            <button onclick="location.reload()" class="block w-full bg-gray-200 hover:bg-gray-300 text-gray-700 px-6 py-3 rounded-lg font-semibold transition duration-200">
                Try Again
            </button>
        </div>

        <div class="mt-8 text-sm text-gray-500">
            <p>Error Code: 500</p>
            <p class="mt-1">Our team has been notified and is working on a fix.</p>
            <p class="mt-1">If the problem persists, please <a href="{{ route('client.tickets.create') }}" class="text-blue-600 hover:underline">contact support</a>.</p>
        </div>
    </div>
</div>
@endsection
