<nav class="bg-white shadow-sm border-b border-gray-200">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="flex h-16 justify-between items-center">
            <!-- Logo -->
            <div class="flex items-center">
                <a href="{{ route('client.dashboard') }}" class="text-xl font-bold text-primary">
                    {{ config('app.name') }}
                </a>
            </div>

            <!-- Desktop Navigation -->
            <div class="hidden md:flex md:space-x-8">
                <a href="{{ route('client.dashboard') }}" class="inline-flex items-center px-1 pt-1 text-sm font-medium {{ request()->routeIs('client.dashboard') ? 'text-primary border-b-2 border-primary' : 'text-gray-500 hover:text-gray-700' }}">
                    Dashboard
                </a>
                <a href="{{ route('client.services.index') }}" class="inline-flex items-center px-1 pt-1 text-sm font-medium {{ request()->routeIs('client.services.*') ? 'text-primary border-b-2 border-primary' : 'text-gray-500 hover:text-gray-700' }}">
                    My Services
                </a>
                <a href="{{ route('client.order.index') }}" class="inline-flex items-center px-1 pt-1 text-sm font-medium {{ request()->routeIs('client.order.*') ? 'text-primary border-b-2 border-primary' : 'text-gray-500 hover:text-gray-700' }}">
                    Order
                </a>
                <a href="{{ route('client.invoices.index') }}" class="inline-flex items-center px-1 pt-1 text-sm font-medium {{ request()->routeIs('client.invoices.*') ? 'text-primary border-b-2 border-primary' : 'text-gray-500 hover:text-gray-700' }}">
                    Invoices
                </a>
                <a href="{{ route('client.tickets.index') }}" class="inline-flex items-center px-1 pt-1 text-sm font-medium {{ request()->routeIs('client.tickets.*') ? 'text-primary border-b-2 border-primary' : 'text-gray-500 hover:text-gray-700' }}">
                    Support
                </a>
            </div>

            <!-- User Menu -->
            <div class="hidden md:flex md:items-center md:space-x-4">
                <div x-data="{ open: false }" class="relative">
                    <button @click="open = !open" class="flex items-center space-x-2 text-sm font-medium text-gray-700 hover:text-gray-900">
                        <div class="h-8 w-8 rounded-full bg-primary flex items-center justify-center text-white font-semibold">
                            {{ substr(auth()->user()->name, 0, 1) }}
                        </div>
                        <span>{{ auth()->user()->name }}</span>
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>

                    <div x-show="open"
                         @click.away="open = false"
                         x-transition:enter="transition ease-out duration-100"
                         x-transition:enter-start="transform opacity-0 scale-95"
                         x-transition:enter-end="transform opacity-100 scale-100"
                         x-transition:leave="transition ease-in duration-75"
                         x-transition:leave-start="transform opacity-100 scale-100"
                         x-transition:leave-end="transform opacity-0 scale-95"
                         class="absolute right-0 z-10 mt-2 w-48 origin-top-right rounded-md bg-white py-1 shadow-lg ring-1 ring-black ring-opacity-5"
                         style="display: none;">
                        <a href="{{ route('client.account.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">My Account</a>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                Sign out
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Mobile menu button -->
            <div class="flex md:hidden">
                <button @click="mobileMenuOpen = !mobileMenuOpen" type="button" class="inline-flex items-center justify-center rounded-md p-2 text-gray-400 hover:bg-gray-100 hover:text-gray-500">
                    <span class="sr-only">Open menu</span>
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Mobile menu -->
    <div x-show="mobileMenuOpen" class="md:hidden" style="display: none;">
        <div class="space-y-1 pb-3 pt-2">
            <a href="{{ route('client.dashboard') }}" class="block border-l-4 py-2 pl-3 pr-4 text-base font-medium {{ request()->routeIs('client.dashboard') ? 'border-primary bg-primary/10 text-primary' : 'border-transparent text-gray-500 hover:bg-gray-50 hover:border-gray-300 hover:text-gray-700' }}">
                Dashboard
            </a>
            <a href="{{ route('client.services.index') }}" class="block border-l-4 py-2 pl-3 pr-4 text-base font-medium {{ request()->routeIs('client.services.*') ? 'border-primary bg-primary/10 text-primary' : 'border-transparent text-gray-500 hover:bg-gray-50 hover:border-gray-300 hover:text-gray-700' }}">
                My Services
            </a>
            <a href="{{ route('client.order.index') }}" class="block border-l-4 py-2 pl-3 pr-4 text-base font-medium {{ request()->routeIs('client.order.*') ? 'border-primary bg-primary/10 text-primary' : 'border-transparent text-gray-500 hover:bg-gray-50 hover:border-gray-300 hover:text-gray-700' }}">
                Order
            </a>
            <a href="{{ route('client.invoices.index') }}" class="block border-l-4 py-2 pl-3 pr-4 text-base font-medium {{ request()->routeIs('client.invoices.*') ? 'border-primary bg-primary/10 text-primary' : 'border-transparent text-gray-500 hover:bg-gray-50 hover:border-gray-300 hover:text-gray-700' }}">
                Invoices
            </a>
            <a href="{{ route('client.tickets.index') }}" class="block border-l-4 py-2 pl-3 pr-4 text-base font-medium {{ request()->routeIs('client.tickets.*') ? 'border-primary bg-primary/10 text-primary' : 'border-transparent text-gray-500 hover:bg-gray-50 hover:border-gray-300 hover:text-gray-700' }}">
                Support
            </a>
        </div>
        <div class="border-t border-gray-200 pb-3 pt-4">
            <div class="flex items-center px-4">
                <div class="flex-shrink-0">
                    <div class="h-10 w-10 rounded-full bg-primary flex items-center justify-center text-white font-semibold">
                        {{ substr(auth()->user()->name, 0, 1) }}
                    </div>
                </div>
                <div class="ml-3">
                    <div class="text-base font-medium text-gray-800">{{ auth()->user()->name }}</div>
                    <div class="text-sm font-medium text-gray-500">{{ auth()->user()->email }}</div>
                </div>
            </div>
            <div class="mt-3 space-y-1">
                <a href="{{ route('client.account.index') }}" class="block px-4 py-2 text-base font-medium text-gray-500 hover:bg-gray-100 hover:text-gray-800">My Account</a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="block w-full text-left px-4 py-2 text-base font-medium text-gray-500 hover:bg-gray-100 hover:text-gray-800">
                        Sign out
                    </button>
                </form>
            </div>
        </div>
    </div>
</nav>
