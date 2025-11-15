@extends('layouts.app-client')

@section('title', 'Configure ' . $product->name)

@section('content')
<div class="container mx-auto px-4 py-8 max-w-5xl">
    <div class="mb-6">
        <div class="flex items-center mb-4">
            <a href="{{ route('client.orders.index') }}" class="text-gray-600 hover:text-gray-900 mr-4">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
            </a>
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Configure Your Service</h1>
                <p class="text-gray-600 mt-1">{{ $product->name }}</p>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Configuration Form -->
        <div class="lg:col-span-2">
            <form method="POST" action="{{ route('client.orders.checkout') }}" id="configureForm">
                @csrf
                <input type="hidden" name="product_id" value="{{ $product->id }}">

                <div class="bg-white rounded-lg shadow space-y-6">
                    <!-- Billing Cycle -->
                    @if($product->billing_cycle != 'one_time')
                    <div class="p-6 border-b">
                        <h2 class="text-lg font-semibold text-gray-900 mb-4">Billing Cycle</h2>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            @php
                            $cycles = [
                                'monthly' => 'Monthly',
                                'quarterly' => 'Quarterly (3 months)',
                                'semi_annually' => 'Semi-Annually (6 months)',
                                'annually' => 'Annually (12 months)',
                                'biennially' => 'Biennially (24 months)',
                                'triennially' => 'Triennially (36 months)'
                            ];
                            @endphp
                            @foreach($cycles as $key => $label)
                            <label class="relative flex items-center p-4 border rounded-lg cursor-pointer hover:bg-gray-50 transition">
                                <input type="radio" name="billing_cycle" value="{{ $key }}" {{ $key == 'monthly' ? 'checked' : '' }} class="mr-3" required>
                                <div>
                                    <p class="font-medium text-gray-900">{{ $label }}</p>
                                    <p class="text-sm text-gray-500">${{ number_format($product->price, 2) }}</p>
                                </div>
                            </label>
                            @endforeach
                        </div>
                    </div>
                    @else
                    <input type="hidden" name="billing_cycle" value="one_time">
                    @endif

                    <!-- Configurable Options -->
                    @if($product->config_options && count(json_decode($product->config_options, true)) > 0)
                    <div class="p-6 border-b">
                        <h2 class="text-lg font-semibold text-gray-900 mb-4">Configurable Options</h2>
                        <div class="space-y-4">
                            @foreach(json_decode($product->config_options, true) as $index => $option)
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">{{ $option['name'] ?? 'Option' }}</label>
                                @if($option['type'] == 'select')
                                <select name="config_options[{{ $index }}]" class="w-full px-4 py-2 border rounded-lg">
                                    @foreach($option['options'] ?? [] as $value => $optionLabel)
                                    <option value="{{ $value }}">{{ $optionLabel }}</option>
                                    @endforeach
                                </select>
                                @elseif($option['type'] == 'text')
                                <input type="text" name="config_options[{{ $index }}]" placeholder="{{ $option['placeholder'] ?? '' }}" class="w-full px-4 py-2 border rounded-lg">
                                @elseif($option['type'] == 'quantity')
                                <input type="number" name="config_options[{{ $index }}]" value="{{ $option['min'] ?? 1 }}" min="{{ $option['min'] ?? 1 }}" max="{{ $option['max'] ?? 100 }}" class="w-full px-4 py-2 border rounded-lg">
                                @endif
                                @if(isset($option['description']))
                                <p class="mt-1 text-xs text-gray-500">{{ $option['description'] }}</p>
                                @endif
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endif

                    <!-- Domain Configuration (for hosting products) -->
                    @if(in_array($product->module, ['cpanel', 'plesk', 'directadmin']))
                    <div class="p-6 border-b">
                        <h2 class="text-lg font-semibold text-gray-900 mb-4">Domain Configuration</h2>
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Domain Type</label>
                                <div class="space-y-2">
                                    <label class="flex items-center">
                                        <input type="radio" name="domain_type" value="register" class="mr-2" checked>
                                        <span>Register a new domain</span>
                                    </label>
                                    <label class="flex items-center">
                                        <input type="radio" name="domain_type" value="transfer" class="mr-2">
                                        <span>Transfer domain from another registrar</span>
                                    </label>
                                    <label class="flex items-center">
                                        <input type="radio" name="domain_type" value="existing" class="mr-2">
                                        <span>Use existing domain</span>
                                    </label>
                                    <label class="flex items-center">
                                        <input type="radio" name="domain_type" value="subdomain" class="mr-2">
                                        <span>Use subdomain (free)</span>
                                    </label>
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Domain Name</label>
                                <input type="text" name="domain" placeholder="example.com" required class="w-full px-4 py-2 border rounded-lg">
                                <p class="mt-1 text-xs text-gray-500">Enter your desired domain name</p>
                            </div>
                        </div>
                    </div>
                    @else
                    <!-- Server Name/Username for other products -->
                    <div class="p-6 border-b">
                        <h2 class="text-lg font-semibold text-gray-900 mb-4">Service Configuration</h2>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Service Username/Identifier</label>
                            <input type="text" name="username" placeholder="Your desired username" class="w-full px-4 py-2 border rounded-lg">
                            <p class="mt-1 text-xs text-gray-500">This will be used to identify your service</p>
                        </div>
                    </div>
                    @endif

                    <!-- Add-ons -->
                    @if($product->addons && count($product->addons) > 0)
                    <div class="p-6 border-b">
                        <h2 class="text-lg font-semibold text-gray-900 mb-4">Available Add-ons</h2>
                        <div class="space-y-3">
                            @foreach($product->addons as $addon)
                            <label class="flex items-center justify-between p-4 border rounded-lg cursor-pointer hover:bg-gray-50 transition">
                                <div class="flex items-center">
                                    <input type="checkbox" name="addons[]" value="{{ $addon->id }}" class="mr-3">
                                    <div>
                                        <p class="font-medium text-gray-900">{{ $addon->name }}</p>
                                        <p class="text-sm text-gray-500">{{ $addon->description }}</p>
                                    </div>
                                </div>
                                <span class="text-blue-600 font-semibold">${{ number_format($addon->price, 2) }}</span>
                            </label>
                            @endforeach
                        </div>
                    </div>
                    @endif

                    <!-- Additional Information -->
                    <div class="p-6">
                        <h2 class="text-lg font-semibold text-gray-900 mb-4">Additional Notes (Optional)</h2>
                        <textarea name="notes" rows="4" placeholder="Any special requirements or notes..." class="w-full px-4 py-2 border rounded-lg"></textarea>
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="mt-6 flex justify-between items-center">
                    <a href="{{ route('client.orders.index') }}" class="text-gray-600 hover:text-gray-900 font-medium">
                        ← Back to Products
                    </a>
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-8 py-3 rounded-lg font-semibold transition duration-200">
                        Continue to Checkout →
                    </button>
                </div>
            </form>
        </div>

        <!-- Order Summary Sidebar -->
        <div class="lg:col-span-1">
            <div class="bg-white rounded-lg shadow sticky top-6">
                <div class="p-6 border-b">
                    <h3 class="text-lg font-semibold text-gray-900">Order Summary</h3>
                </div>
                <div class="p-6 space-y-4">
                    <div>
                        <p class="text-sm text-gray-500">Product</p>
                        <p class="font-semibold text-gray-900">{{ $product->name }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Price</p>
                        <p class="font-semibold text-gray-900">${{ number_format($product->price, 2) }}</p>
                    </div>
                    @if($product->setup_fee > 0)
                    <div>
                        <p class="text-sm text-gray-500">Setup Fee</p>
                        <p class="font-semibold text-gray-900">${{ number_format($product->setup_fee, 2) }}</p>
                    </div>
                    @endif
                    <div class="pt-4 border-t">
                        <div class="flex justify-between items-center">
                            <p class="text-sm text-gray-500">Total</p>
                            <p class="text-2xl font-bold text-blue-600">${{ number_format($product->price + $product->setup_fee, 2) }}</p>
                        </div>
                        <p class="text-xs text-gray-500 mt-1">Billed {{ $product->billing_cycle }}</p>
                    </div>
                </div>

                <!-- Product Features -->
                @if($product->features)
                <div class="p-6 border-t">
                    <h4 class="font-semibold text-gray-900 mb-3">What's Included</h4>
                    <div class="space-y-2">
                        @foreach(json_decode($product->features, true) ?? [] as $feature)
                        <div class="flex items-center text-sm text-gray-700">
                            <svg class="w-4 h-4 text-green-600 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            <span>{{ $feature }}</span>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif

                <!-- Trust Badges -->
                <div class="p-6 border-t bg-gray-50">
                    <div class="space-y-3 text-sm">
                        <div class="flex items-center text-green-600">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                            </svg>
                            <span>Secure Payment</span>
                        </div>
                        <div class="flex items-center text-green-600">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                            </svg>
                            <span>Instant Activation</span>
                        </div>
                        <div class="flex items-center text-green-600">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <span>24/7 Support</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
