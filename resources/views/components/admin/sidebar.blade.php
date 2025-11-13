<div class="flex flex-col h-full">
    <!-- Logo & Brand -->
    <div class="flex items-center justify-center h-16 px-4 border-b border-gray-200">
        <h1 class="text-xl font-bold text-primary">{{ config('app.name') }}</h1>
    </div>

    <!-- Navigation -->
    <nav class="flex-1 overflow-y-auto px-2 py-4 space-y-1">
        <!-- Dashboard -->
        <a href="{{ route('admin.dashboard') }}" class="flex items-center px-3 py-2 text-sm font-medium rounded-lg {{ request()->routeIs('admin.dashboard') ? 'bg-primary text-white' : 'text-gray-700 hover:bg-gray-100' }}">
            <svg class="w-5 h-5 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
            </svg>
            Dashboard
        </a>

        <!-- Financial Tracker -->
        <a href="{{ route('admin.financial.index') }}" class="flex items-center px-3 py-2 text-sm font-medium rounded-lg {{ request()->routeIs('admin.financial.*') ? 'bg-primary text-white' : 'text-gray-700 hover:bg-gray-100' }}">
            <svg class="w-5 h-5 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
            </svg>
            Financial
        </a>

        <!-- Divider -->
        <div class="border-t border-gray-200 my-2"></div>

        <!-- Users -->
        <a href="{{ route('admin.users.index') }}" class="flex items-center px-3 py-2 text-sm font-medium rounded-lg {{ request()->routeIs('admin.users.*') ? 'bg-primary text-white' : 'text-gray-700 hover:bg-gray-100' }}">
            <svg class="w-5 h-5 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
            </svg>
            Users
        </a>

        <!-- Products -->
        <a href="{{ route('admin.products.index') }}" class="flex items-center px-3 py-2 text-sm font-medium rounded-lg {{ request()->routeIs('admin.products.*') || request()->routeIs('admin.product-groups.*') ? 'bg-primary text-white' : 'text-gray-700 hover:bg-gray-100' }}">
            <svg class="w-5 h-5 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
            </svg>
            Products
        </a>

        <!-- Services -->
        <a href="{{ route('admin.services.index') }}" class="flex items-center px-3 py-2 text-sm font-medium rounded-lg {{ request()->routeIs('admin.services.*') ? 'bg-primary text-white' : 'text-gray-700 hover:bg-gray-100' }}">
            <svg class="w-5 h-5 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2m-2-4h.01M17 16h.01" />
            </svg>
            Services
        </a>

        <!-- Invoices -->
        <a href="{{ route('admin.invoices.index') }}" class="flex items-center px-3 py-2 text-sm font-medium rounded-lg {{ request()->routeIs('admin.invoices.*') ? 'bg-primary text-white' : 'text-gray-700 hover:bg-gray-100' }}">
            <svg class="w-5 h-5 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            Invoices
        </a>

        <!-- Payments -->
        <a href="{{ route('admin.payments.index') }}" class="flex items-center px-3 py-2 text-sm font-medium rounded-lg {{ request()->routeIs('admin.payments.*') ? 'bg-primary text-white' : 'text-gray-700 hover:bg-gray-100' }}">
            <svg class="w-5 h-5 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
            </svg>
            Payments
        </a>

        <!-- Tickets -->
        <a href="{{ route('admin.tickets.index') }}" class="flex items-center px-3 py-2 text-sm font-medium rounded-lg {{ request()->routeIs('admin.tickets.*') ? 'bg-primary text-white' : 'text-gray-700 hover:bg-gray-100' }}">
            <svg class="w-5 h-5 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z" />
            </svg>
            Tickets
        </a>

        <!-- Divider -->
        <div class="border-t border-gray-200 my-2"></div>

        <!-- Extensions -->
        <a href="{{ route('admin.extensions.index') }}" class="flex items-center px-3 py-2 text-sm font-medium rounded-lg {{ request()->routeIs('admin.extensions.*') ? 'bg-primary text-white' : 'text-gray-700 hover:bg-gray-100' }}">
            <svg class="w-5 h-5 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 4a2 2 0 114 0v1a1 1 0 001 1h3a1 1 0 011 1v3a1 1 0 01-1 1h-1a2 2 0 100 4h1a1 1 0 011 1v3a1 1 0 01-1 1h-3a1 1 0 01-1-1v-1a2 2 0 10-4 0v1a1 1 0 01-1 1H7a1 1 0 01-1-1v-3a1 1 0 00-1-1H4a2 2 0 110-4h1a1 1 0 001-1V7a1 1 0 011-1h3a1 1 0 001-1V4z" />
            </svg>
            Extensions
        </a>

        <!-- Health Check -->
        <a href="{{ route('admin.health.index') }}" class="flex items-center px-3 py-2 text-sm font-medium rounded-lg {{ request()->routeIs('admin.health.*') ? 'bg-primary text-white' : 'text-gray-700 hover:bg-gray-100' }}">
            <svg class="w-5 h-5 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
            </svg>
            Health Check
        </a>

        <!-- Security -->
        <a href="{{ route('admin.security.index') }}" class="flex items-center px-3 py-2 text-sm font-medium rounded-lg {{ request()->routeIs('admin.security.*') ? 'bg-primary text-white' : 'text-gray-700 hover:bg-gray-100' }}">
            <svg class="w-5 h-5 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
            </svg>
            Security
        </a>

        <!-- Settings -->
        <a href="{{ route('admin.settings.index') }}" class="flex items-center px-3 py-2 text-sm font-medium rounded-lg {{ request()->routeIs('admin.settings.*') || request()->routeIs('admin.theme.*') || request()->routeIs('admin.menus.*') ? 'bg-primary text-white' : 'text-gray-700 hover:bg-gray-100' }}">
            <svg class="w-5 h-5 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
            </svg>
            Settings
        </a>
    </nav>

    <!-- User Profile -->
    <div class="border-t border-gray-200 p-4">
        <div class="flex items-center">
            <div class="flex-shrink-0">
                <div class="h-10 w-10 rounded-full bg-primary flex items-center justify-center text-white font-semibold">
                    {{ substr(auth()->user()->name, 0, 1) }}
                </div>
            </div>
            <div class="ml-3 flex-1 min-w-0">
                <p class="text-sm font-medium text-gray-900 truncate">{{ auth()->user()->name }}</p>
                <p class="text-xs text-gray-500 truncate">{{ auth()->user()->role->display_name }}</p>
            </div>
        </div>
    </div>
</div>
