<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Welcome') - {{ config('app.name') }}</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700" rel="stylesheet" />
    <script src="https://cdn.tailwindcss.com"></script>

    <style>
        :root {
            --color-primary: {{ $themeSettings->primary_color ?? '#3b82f6' }};
            --color-secondary: {{ $themeSettings->secondary_color ?? '#10b981' }};
        }

        body { font-family: 'Inter', sans-serif; }
        .bg-primary { background-color: var(--color-primary); }
        .text-primary { color: var(--color-primary); }
        .border-primary { border-color: var(--color-primary); }
    </style>

    @stack('styles')
</head>
<body class="bg-gray-50 antialiased">
    <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0">
        <!-- Logo -->
        <div class="mb-6">
            <a href="/">
                <h1 class="text-3xl font-bold text-primary">
                    {{ config('app.name') }}
                </h1>
            </a>
        </div>

        <!-- Card -->
        <div class="w-full sm:max-w-md px-6 py-8 bg-white shadow-lg rounded-lg">
            @if(session('success'))
            <div class="mb-4 bg-green-50 border-l-4 border-green-400 p-4 rounded">
                <p class="text-sm text-green-700">{{ session('success') }}</p>
            </div>
            @endif

            @if(session('error'))
            <div class="mb-4 bg-red-50 border-l-4 border-red-400 p-4 rounded">
                <p class="text-sm text-red-700">{{ session('error') }}</p>
            </div>
            @endif

            @yield('content')
        </div>

        <!-- Footer Links -->
        <div class="mt-6 text-center text-sm text-gray-600">
            <a href="/" class="hover:text-primary transition">Home</a>
            <span class="mx-2">•</span>
            <a href="#" class="hover:text-primary transition">Terms</a>
            <span class="mx-2">•</span>
            <a href="#" class="hover:text-primary transition">Privacy</a>
        </div>
    </div>

    @stack('scripts')
</body>
</html>
