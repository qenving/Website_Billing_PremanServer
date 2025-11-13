<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Dashboard') - {{ config('app.name') }}</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700" rel="stylesheet" />
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <style>
        :root {
            --color-primary: {{ $themeSettings->primary_color ?? '#3b82f6' }};
            --color-secondary: {{ $themeSettings->secondary_color ?? '#10b981' }};
            --color-background: {{ $themeSettings->background_color ?? '#ffffff' }};
            --color-surface: {{ $themeSettings->surface_color ?? '#f9fafb' }};
            --color-text: {{ $themeSettings->text_color ?? '#111827' }};
        }

        body { font-family: 'Inter', sans-serif; }
        .bg-primary { background-color: var(--color-primary); }
        .bg-secondary { background-color: var(--color-secondary); }
        .text-primary { color: var(--color-primary); }
        .text-secondary { color: var(--color-secondary); }
        .border-primary { border-color: var(--color-primary); }

        @if(isset($themeSettings->custom_css))
        {!! $themeSettings->custom_css !!}
        @endif
    </style>

    @stack('styles')
</head>
<body class="bg-gray-50 antialiased">
    <div x-data="{ mobileMenuOpen: false }" class="min-h-screen">
        <!-- Top Navigation -->
        @include('components.client.navbar')

        <!-- Main Content -->
        <main class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-8">
            <!-- Page Header -->
            @if(isset($header))
            <div class="mb-8">
                {{ $header }}
            </div>
            @endif

            <!-- Flash Messages -->
            @if(session('success'))
            <div class="mb-6 bg-green-50 border-l-4 border-green-400 p-4 rounded-lg">
                <div class="flex items-center">
                    <svg class="h-5 w-5 text-green-400 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <p class="text-sm text-green-700">{{ session('success') }}</p>
                </div>
            </div>
            @endif

            @if(session('error'))
            <div class="mb-6 bg-red-50 border-l-4 border-red-400 p-4 rounded-lg">
                <div class="flex items-center">
                    <svg class="h-5 w-5 text-red-400 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <p class="text-sm text-red-700">{{ session('error') }}</p>
                </div>
            </div>
            @endif

            @if(session('warning'))
            <div class="mb-6 bg-yellow-50 border-l-4 border-yellow-400 p-4 rounded-lg">
                <div class="flex items-center">
                    <svg class="h-5 w-5 text-yellow-400 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                    <p class="text-sm text-yellow-700">{{ session('warning') }}</p>
                </div>
            </div>
            @endif

            <!-- Content -->
            @yield('content')
        </main>

        <!-- Footer -->
        @include('components.client.footer')
    </div>

    @stack('scripts')
</body>
</html>
