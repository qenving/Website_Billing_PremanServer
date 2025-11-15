@extends('layouts.app-client')

@section('title', 'Checkout')

@section('content')
<div class="container mx-auto px-4 py-8 max-w-5xl">
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-900">Checkout</h1>
        <p class="text-gray-600 mt-1">Review your order and complete payment</p>
    </div>

    <form method="POST" action="{{ route('client.orders.process') }}" id="checkoutForm">
        @csrf

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Main Content -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Order Summary -->
                <div class="bg-white rounded-lg shadow">
                    <div class="p-6 border-b">
                        <h2 class="text-lg font-semibold text-gray-900">Order Summary</h2>
                    </div>
                    <div class="p-6">
                        <div class="space-y-4">
                            <!-- Product Info -->
                            <div class="flex justify-between items-start pb-4 border-b">
                                <div class="flex-1">
                                    <h3 class="font-semibold text-gray-900">{{ $product->name ?? 'Product' }}</h3>
                                    <p class="text-sm text-gray-500 mt-1">{{ $billingCycle ?? 'Monthly' }} Billing</p>
                                    @if(isset($domain))
                                    <p class="text-sm text-gray-600 mt-1">Domain: {{ $domain }}</p>
                                    @endif
                                </div>
                                <div class="text-right">
                                    <p class="font-semibold text-gray-900">${{ number_format($productPrice ?? 0, 2) }}</p>
                                </div>
                            </div>

                            <!-- Setup Fee -->
                            @if(isset($setupFee) && $setupFee > 0)
                            <div class="flex justify-between items-center pb-4 border-b">
                                <p class="text-gray-700">Setup Fee</p>
                                <p class="font-semibold text-gray-900">${{ number_format($setupFee, 2) }}</p>
                            </div>
                            @endif

                            <!-- Add-ons -->
                            @if(isset($addons) && count($addons) > 0)
                            <div class="pb-4 border-b">
                                <p class="text-gray-700 font-medium mb-2">Add-ons:</p>
                                @foreach($addons as $addon)
                                <div class="flex justify-between items-center ml-4 mb-2">
                                    <p class="text-sm text-gray-600">{{ $addon->name ?? 'Add-on' }}</p>
                                    <p class="text-sm font-semibold text-gray-900">${{ number_format($addon->price ?? 0, 2) }}</p>
                                </div>
                                @endforeach
                            </div>
                            @endif

                            <!-- Subtotal -->
                            <div class="flex justify-between items-center">
                                <p class="text-gray-700">Subtotal</p>
                                <p class="font-semibold text-gray-900">${{ number_format($subtotal ?? 0, 2) }}</p>
                            </div>

                            <!-- Tax -->
                            @if(isset($tax) && $tax > 0)
                            <div class="flex justify-between items-center">
                                <p class="text-gray-700">Tax ({{ $taxRate ?? 0 }}%)</p>
                                <p class="font-semibold text-gray-900">${{ number_format($tax, 2) }}</p>
                            </div>
                            @endif

                            <!-- Total -->
                            <div class="flex justify-between items-center pt-4 border-t">
                                <p class="text-lg font-semibold text-gray-900">Total Due Today</p>
                                <p class="text-2xl font-bold text-blue-600">${{ number_format($total ?? 0, 2) }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Payment Method -->
                <div class="bg-white rounded-lg shadow">
                    <div class="p-6 border-b">
                        <h2 class="text-lg font-semibold text-gray-900">Payment Method</h2>
                    </div>
                    <div class="p-6">
                        <div class="space-y-3">
                            @forelse($paymentGateways ?? [] as $gateway)
                            <label class="flex items-center p-4 border rounded-lg cursor-pointer hover:bg-gray-50 transition {{ $loop->first ? 'border-blue-500 bg-blue-50' : '' }}">
                                <input type="radio" name="payment_gateway" value="{{ $gateway->slug }}" {{ $loop->first ? 'checked' : '' }} required class="mr-3">
                                <div class="flex-1">
                                    <div class="flex items-center">
                                        @if($gateway->logo)
                                        <img src="{{ $gateway->logo }}" alt="{{ $gateway->name }}" class="h-6 mr-3">
                                        @endif
                                        <span class="font-medium text-gray-900">{{ $gateway->name }}</span>
                                    </div>
                                    @if($gateway->description)
                                    <p class="text-sm text-gray-500 mt-1">{{ $gateway->description }}</p>
                                    @endif
                                </div>
                                @if($gateway->fee_percentage > 0 || $gateway->fee_fixed > 0)
                                <span class="text-xs text-gray-500">
                                    @if($gateway->fee_percentage > 0)
                                    +{{ $gateway->fee_percentage }}%
                                    @endif
                                    @if($gateway->fee_fixed > 0)
                                    +${{ number_format($gateway->fee_fixed, 2) }}
                                    @endif
                                    fee
                                </span>
                                @endif
                            </label>
                            @empty
                            <div class="text-center py-8 text-gray-500">
                                <p>No payment methods available</p>
                            </div>
                            @endforelse
                        </div>
                    </div>
                </div>

                <!-- Billing Information -->
                <div class="bg-white rounded-lg shadow">
                    <div class="p-6 border-b">
                        <h2 class="text-lg font-semibold text-gray-900">Billing Information</h2>
                    </div>
                    <div class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">First Name</label>
                                <input type="text" name="first_name" value="{{ auth()->user()->name }}" required class="w-full px-4 py-2 border rounded-lg">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Last Name</label>
                                <input type="text" name="last_name" class="w-full px-4 py-2 border rounded-lg">
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                                <input type="email" name="email" value="{{ auth()->user()->email }}" required class="w-full px-4 py-2 border rounded-lg">
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Address</label>
                                <input type="text" name="address" class="w-full px-4 py-2 border rounded-lg">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">City</label>
                                <input type="text" name="city" class="w-full px-4 py-2 border rounded-lg">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Postal Code</label>
                                <input type="text" name="postal_code" class="w-full px-4 py-2 border rounded-lg">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Country</label>
                                <select name="country" class="w-full px-4 py-2 border rounded-lg">
                                    <option value="">Select Country...</option>
                                    <option value="US">United States</option>
                                    <option value="GB">United Kingdom</option>
                                    <option value="CA">Canada</option>
                                    <option value="AU">Australia</option>
                                    <option value="ID">Indonesia</option>
                                    <!-- Add more countries as needed -->
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Phone</label>
                                <input type="tel" name="phone" class="w-full px-4 py-2 border rounded-lg">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Terms and Conditions -->
                <div class="bg-white rounded-lg shadow p-6">
                    <label class="flex items-start cursor-pointer">
                        <input type="checkbox" name="agree_tos" required class="mt-1 mr-3">
                        <span class="text-sm text-gray-700">
                            I have read and agree to the <a href="#" class="text-blue-600 hover:underline">Terms of Service</a> and <a href="#" class="text-blue-600 hover:underline">Privacy Policy</a>
                        </span>
                    </label>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-lg shadow sticky top-6">
                    <div class="p-6 border-b">
                        <h3 class="text-lg font-semibold text-gray-900">Order Total</h3>
                    </div>
                    <div class="p-6 space-y-4">
                        <div class="text-center pb-6 border-b">
                            <p class="text-sm text-gray-500 mb-1">Amount Due</p>
                            <p class="text-3xl font-bold text-blue-600">${{ number_format($total ?? 0, 2) }}</p>
                            <p class="text-xs text-gray-500 mt-1">Billed {{ $billingCycle ?? 'monthly' }}</p>
                        </div>

                        <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg font-semibold transition duration-200">
                            Complete Order
                        </button>

                        <p class="text-xs text-gray-500 text-center">You will be redirected to the payment gateway</p>
                    </div>

                    <div class="p-6 border-t bg-gray-50">
                        <div class="space-y-3 text-sm">
                            <div class="flex items-center text-green-600">
                                <svg class="w-5 h-5 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                                </svg>
                                <span>256-bit SSL Encryption</span>
                            </div>
                            <div class="flex items-center text-green-600">
                                <svg class="w-5 h-5 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                                </svg>
                                <span>Secure Payment Processing</span>
                            </div>
                            <div class="flex items-center text-green-600">
                                <svg class="w-5 h-5 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <span>30-Day Money-Back</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Support -->
                <div class="mt-6 bg-blue-50 border border-blue-200 rounded-lg p-6">
                    <h4 class="font-semibold text-blue-900 mb-2">Need Help?</h4>
                    <p class="text-sm text-blue-700 mb-3">Our support team is here to assist you</p>
                    <a href="{{ route('client.tickets.create') }}" class="block w-full text-center bg-white hover:bg-blue-50 text-blue-600 px-4 py-2 rounded-lg font-medium text-sm border border-blue-300 transition">
                        Contact Support
                    </a>
                </div>
            </div>
        </div>

        <!-- Back Button -->
        <div class="mt-6">
            <a href="{{ route('client.orders.configure', $product ?? 1) }}" class="text-gray-600 hover:text-gray-900 font-medium">
                ← Back to Configuration
            </a>
        </div>
    </form>
</div>

<!-- Payment Processing Overlay -->
<div id="processingOverlay" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white rounded-lg p-8 text-center">
        <div class="animate-spin rounded-full h-16 w-16 border-b-2 border-blue-600 mx-auto mb-4"></div>
        <p class="text-lg font-semibold text-gray-900">Processing your payment...</p>
        <p class="text-sm text-gray-500 mt-2">Please do not close this window</p>
    </div>
</div>

<script>
document.getElementById('checkoutForm').addEventListener('submit', function() {
    document.getElementById('processingOverlay').classList.remove('hidden');
});
</script>
@endsection
