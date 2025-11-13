<div class="flex flex-1 justify-between px-4">
    <div class="flex flex-1 items-center">
        <!-- Search (optional, can be added later) -->
    </div>

    <div class="ml-4 flex items-center space-x-4">
        <!-- Notifications -->
        <button type="button" class="rounded-full p-1 text-gray-400 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2">
            <span class="sr-only">View notifications</span>
            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0" />
            </svg>
        </button>

        <!-- Profile dropdown -->
        <div x-data="{ open: false }" class="relative">
            <button @click="open = !open" type="button" class="flex items-center max-w-xs rounded-full text-sm focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2">
                <span class="sr-only">Open user menu</span>
                <div class="h-8 w-8 rounded-full bg-primary flex items-center justify-center text-white font-semibold">
                    {{ substr(auth()->user()->name, 0, 1) }}
                </div>
            </button>

            <div x-show="open"
                 @click.away="open = false"
                 x-transition:enter="transition ease-out duration-100"
                 x-transition:enter-start="transform opacity-0 scale-95"
                 x-transition:enter-end="transform opacity-100 scale-100"
                 x-transition:leave="transition ease-in duration-75"
                 x-transition:leave-start="transform opacity-100 scale-100"
                 x-transition:leave-end="transform opacity-0 scale-95"
                 class="absolute right-0 z-10 mt-2 w-48 origin-top-right rounded-md bg-white py-1 shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none"
                 style="display: none;">
                <div class="px-4 py-2 border-b border-gray-100">
                    <p class="text-sm font-medium text-gray-900">{{ auth()->user()->name }}</p>
                    <p class="text-xs text-gray-500">{{ auth()->user()->email }}</p>
                </div>
                <a href="{{ route('admin.settings.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Settings</a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                        Sign out
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
