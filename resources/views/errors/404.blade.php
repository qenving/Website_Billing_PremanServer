@extends('layouts.guest')

@section('title', '404 - Page Not Found')

@section('content')
<div class="min-h-screen flex items-center justify-center bg-gray-100 px-4">
    <div class="max-w-md w-full text-center">
        <div class="mb-8">
            <h1 class="text-9xl font-bold text-blue-600">404</h1>
            <h2 class="text-3xl font-bold text-gray-900 mt-4">Page Not Found</h2>
            <p class="text-gray-600 mt-4">Sorry, we couldn't find the page you're looking for.</p>
        </div>

        <div class="mb-8">
            <svg class="mx-auto h-48 w-48 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
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
            <p>Error Code: 404</p>
            <p class="mt-1">If you believe this is a mistake, please <a href="{{ route('client.tickets.create') }}" class="text-blue-600 hover:underline">contact support</a>.</p>
        </div>
    </div>
</div>
@endsection
