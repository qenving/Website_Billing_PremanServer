<footer class="bg-white border-t border-gray-200 mt-12">
    <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
            <!-- Brand -->
            <div>
                <h3 class="text-lg font-bold text-primary mb-4">{{ config('app.name') }}</h3>
                <p class="text-sm text-gray-600">
                    Professional hosting and billing management platform.
                </p>
            </div>

            <!-- Quick Links -->
            <div>
                <h4 class="text-sm font-semibold text-gray-900 mb-4">Quick Links</h4>
                <ul class="space-y-2">
                    <li><a href="{{ route('client.dashboard') }}" class="text-sm text-gray-600 hover:text-primary">Dashboard</a></li>
                    <li><a href="{{ route('client.services.index') }}" class="text-sm text-gray-600 hover:text-primary">My Services</a></li>
                    <li><a href="{{ route('client.order.index') }}" class="text-sm text-gray-600 hover:text-primary">Order New Service</a></li>
                </ul>
            </div>

            <!-- Support -->
            <div>
                <h4 class="text-sm font-semibold text-gray-900 mb-4">Support</h4>
                <ul class="space-y-2">
                    <li><a href="{{ route('client.tickets.index') }}" class="text-sm text-gray-600 hover:text-primary">Support Tickets</a></li>
                    <li><a href="#" class="text-sm text-gray-600 hover:text-primary">Knowledge Base</a></li>
                    <li><a href="#" class="text-sm text-gray-600 hover:text-primary">Contact Us</a></li>
                </ul>
            </div>

            <!-- Legal -->
            <div>
                <h4 class="text-sm font-semibold text-gray-900 mb-4">Legal</h4>
                <ul class="space-y-2">
                    <li><a href="#" class="text-sm text-gray-600 hover:text-primary">Terms of Service</a></li>
                    <li><a href="#" class="text-sm text-gray-600 hover:text-primary">Privacy Policy</a></li>
                    <li><a href="#" class="text-sm text-gray-600 hover:text-primary">Refund Policy</a></li>
                </ul>
            </div>
        </div>

        <div class="mt-8 border-t border-gray-200 pt-8 text-center">
            <p class="text-sm text-gray-500">
                &copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.
            </p>
        </div>
    </div>
</footer>
