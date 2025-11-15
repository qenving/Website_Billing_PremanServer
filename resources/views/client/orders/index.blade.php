@extends('layouts.app-client')

@section('title', 'Order New Service')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="mb-8 text-center">
        <h1 class="text-4xl font-bold text-gray-900">Order New Service</h1>
        <p class="text-gray-600 mt-2 text-lg">Choose from our wide range of products and services</p>
    </div>

    <!-- Product Groups -->
    @forelse($productGroups ?? [] as $group)
    <div class="mb-12">
        <div class="mb-6">
            <h2 class="text-2xl font-bold text-gray-900">{{ $group->name }}</h2>
            @if($group->description)
            <p class="text-gray-600 mt-2">{{ $group->description }}</p>
            @endif
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @forelse($group->products->where('is_active', true) as $product)
            <div class="bg-white rounded-lg shadow-md hover:shadow-xl transition duration-300 overflow-hidden">
                <div class="p-6">
                    <h3 class="text-xl font-bold text-gray-900 mb-2">{{ $product->name }}</h3>

                    @if($product->description)
                    <p class="text-gray-600 text-sm mb-4 line-clamp-3">{{ $product->description }}</p>
                    @endif

                    <!-- Pricing -->
                    <div class="mb-4">
                        <div class="flex items-baseline">
                            <span class="text-3xl font-bold text-blue-600">${{ number_format($product->price, 2) }}</span>
                            <span class="ml-2 text-gray-500">/ {{ $product->billing_cycle }}</span>
                        </div>
                        @if($product->setup_fee > 0)
                        <p class="text-sm text-gray-500 mt-1">+ ${{ number_format($product->setup_fee, 2) }} setup fee</p>
                        @endif
                    </div>

                    <!-- Features -->
                    @if($product->features)
                    <div class="mb-4 space-y-2">
                        @foreach(array_slice(json_decode($product->features, true) ?? [], 0, 5) as $feature)
                        <div class="flex items-center text-sm text-gray-700">
                            <svg class="w-4 h-4 text-green-600 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            <span>{{ $feature }}</span>
                        </div>
                        @endforeach
                        @if(count(json_decode($product->features, true) ?? []) > 5)
                        <p class="text-xs text-gray-500 mt-1">+ {{ count(json_decode($product->features, true)) - 5 }} more features</p>
                        @endif
                    </div>
                    @endif

                    <!-- Stock Status -->
                    @if($product->stock_enabled)
                    <div class="mb-4">
                        @if($product->stock_quantity > 0)
                        <span class="text-xs text-green-600 font-medium">✓ In Stock ({{ $product->stock_quantity }} available)</span>
                        @else
                        <span class="text-xs text-red-600 font-medium">✗ Out of Stock</span>
                        @endif
                    </div>
                    @endif

                    <!-- Order Button -->
                    @if(!$product->stock_enabled || $product->stock_quantity > 0)
                    <a href="{{ route('client.orders.configure', $product) }}" class="block w-full text-center bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg font-semibold transition duration-200">
                        Order Now
                    </a>
                    @else
                    <button disabled class="block w-full text-center bg-gray-300 text-gray-500 px-6 py-3 rounded-lg font-semibold cursor-not-allowed">
                        Out of Stock
                    </button>
                    @endif
                </div>

                <!-- Popular Badge -->
                @if($product->is_featured)
                <div class="absolute top-0 right-0 bg-yellow-500 text-white px-3 py-1 text-xs font-bold rounded-bl-lg">
                    POPULAR
                </div>
                @endif
            </div>
            @empty
            <div class="col-span-full text-center py-8">
                <p class="text-gray-500">No products available in this category</p>
            </div>
            @endforelse
        </div>
    </div>
    @empty
    <div class="text-center py-16">
        <svg class="mx-auto h-16 w-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
        </svg>
        <h3 class="mt-4 text-xl font-medium text-gray-900">No Products Available</h3>
        <p class="mt-2 text-gray-500">Products are currently being updated. Please check back later.</p>
    </div>
    @endforelse

    <!-- Benefits Section -->
    <div class="mt-16 bg-gradient-to-r from-blue-600 to-blue-800 rounded-lg p-8 text-white">
        <h2 class="text-3xl font-bold mb-6 text-center">Why Choose Us?</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <div class="text-center">
                <div class="bg-white bg-opacity-20 rounded-full w-16 h-16 flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                </div>
                <h3 class="text-xl font-semibold mb-2">Instant Activation</h3>
                <p class="text-blue-100">Most services are automatically provisioned within minutes</p>
            </div>
            <div class="text-center">
                <div class="bg-white bg-opacity-20 rounded-full w-16 h-16 flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <h3 class="text-xl font-semibold mb-2">24/7 Support</h3>
                <p class="text-blue-100">Our expert support team is always ready to help you</p>
            </div>
            <div class="text-center">
                <div class="bg-white bg-opacity-20 rounded-full w-16 h-16 flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                    </svg>
                </div>
                <h3 class="text-xl font-semibold mb-2">Money-Back Guarantee</h3>
                <p class="text-blue-100">Not satisfied? Get a full refund within 30 days</p>
            </div>
        </div>
    </div>

    <!-- FAQ Section -->
    <div class="mt-12 bg-white rounded-lg shadow p-8">
        <h2 class="text-2xl font-bold text-gray-900 mb-6">Frequently Asked Questions</h2>
        <div class="space-y-4">
            <div class="border-b pb-4">
                <h3 class="font-semibold text-gray-900 mb-2">How long does it take to activate my service?</h3>
                <p class="text-gray-600 text-sm">Most services with auto-provisioning are activated instantly after payment. Manual provisioning typically takes 1-24 hours.</p>
            </div>
            <div class="border-b pb-4">
                <h3 class="font-semibold text-gray-900 mb-2">What payment methods do you accept?</h3>
                <p class="text-gray-600 text-sm">We accept various payment methods including credit/debit cards, PayPal, and other local payment gateways.</p>
            </div>
            <div class="border-b pb-4">
                <h3 class="font-semibold text-gray-900 mb-2">Can I upgrade or downgrade my service later?</h3>
                <p class="text-gray-600 text-sm">Yes! You can upgrade or downgrade your service at any time. Contact our support team for assistance.</p>
            </div>
            <div class="pb-4">
                <h3 class="font-semibold text-gray-900 mb-2">Is there a setup fee?</h3>
                <p class="text-gray-600 text-sm">Some products may have a one-time setup fee, which is clearly displayed in the product details.</p>
            </div>
        </div>
    </div>
</div>
@endsection
