<div class="relative inline-block text-left" x-data="{ open: false }">
    <button @click="open = !open" type="button" class="inline-flex items-center gap-2 px-3 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
        <!-- Flag Icon -->
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5h12M9 3v2m1.048 9.5A18.022 18.022 0 016.412 9m6.088 9h7M11 21l5-10 5 10M12.751 5C11.783 10.77 8.07 15.61 3 18.129"/>
        </svg>

        <!-- Current Language -->
        <span>
            @if(app()->getLocale() === 'en')
                English
            @elseif(app()->getLocale() === 'id')
                Indonesia
            @else
                {{ strtoupper(app()->getLocale()) }}
            @endif
        </span>

        <!-- Dropdown Arrow -->
        <svg class="w-4 h-4" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
        </svg>
    </button>

    <!-- Dropdown Menu -->
    <div x-show="open"
         @click.away="open = false"
         x-transition:enter="transition ease-out duration-100"
         x-transition:enter-start="transform opacity-0 scale-95"
         x-transition:enter-end="transform opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-75"
         x-transition:leave-start="transform opacity-100 scale-100"
         x-transition:leave-end="transform opacity-0 scale-95"
         class="absolute right-0 z-10 mt-2 w-48 origin-top-right rounded-md bg-white shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none"
         style="display: none;">
        <div class="py-1">
            @foreach(config('app.supported_locales', ['en']) as $locale)
                <a href="{{ route('language.switch', $locale) }}"
                   class="flex items-center gap-3 px-4 py-2 text-sm {{ app()->getLocale() === $locale ? 'bg-blue-50 text-blue-700 font-semibold' : 'text-gray-700 hover:bg-gray-100' }}">
                    <!-- Checkmark for active language -->
                    @if(app()->getLocale() === $locale)
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                        </svg>
                    @else
                        <span class="w-4 h-4"></span>
                    @endif

                    <!-- Language Name -->
                    @if($locale === 'en')
                        English
                    @elseif($locale === 'id')
                        Indonesia
                    @else
                        {{ strtoupper($locale) }}
                    @endif
                </a>
            @endforeach
        </div>
    </div>
</div>

<!-- Include Alpine.js if not already included -->
@once
    @push('scripts')
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    @endpush
@endonce
