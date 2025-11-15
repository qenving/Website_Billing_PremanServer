@extends('layouts.app-admin')

@section('title', 'Services Report')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Services Report</h1>
            <p class="text-gray-600 mt-1">Service metrics and performance</p>
        </div>
        <a href="{{ route('admin.reports.index') }}" class="text-blue-600 hover:text-blue-700">
            ← Back to Reports
        </a>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
        <div class="bg-white rounded-lg shadow p-6">
            <p class="text-gray-500 text-sm">Total Services</p>
            <p class="text-3xl font-bold text-gray-900 mt-2">{{ $summary['total_services'] }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-6">
            <p class="text-gray-500 text-sm">Active Services</p>
            <p class="text-3xl font-bold text-green-600 mt-2">{{ $summary['active_services'] }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-6">
            <p class="text-gray-500 text-sm">Monthly Recurring Revenue</p>
            <p class="text-3xl font-bold text-blue-600 mt-2">${{ number_format($summary['mrr'], 2) }}</p>
            <p class="text-xs text-gray-500 mt-1">ARR: ${{ number_format($summary['arr'], 2) }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-6">
            <p class="text-gray-500 text-sm">Churn Rate</p>
            <p class="text-3xl font-bold text-red-600 mt-2">{{ number_format($summary['churn_rate'], 1) }}%</p>
            <p class="text-xs text-gray-500 mt-1">Last 30 days</p>
        </div>
    </div>

    <!-- Status Breakdown -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
        <div class="bg-white rounded-lg shadow">
            <div class="p-6 border-b">
                <h2 class="text-lg font-semibold text-gray-900">Services by Status</h2>
            </div>
            <div class="p-6">
                <div class="space-y-4">
                    @foreach($statusBreakdown as $status => $count)
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <span class="font-semibold text-gray-900">{{ ucfirst($status) }}</span>
                            <span class="text-lg font-bold">{{ $count }}</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-3">
                            @php
                            $colors = [
                                'active' => 'bg-green-600',
                                'pending' => 'bg-yellow-600',
                                'suspended' => 'bg-orange-600',
                                'terminated' => 'bg-red-600',
                                'cancelled' => 'bg-gray-600',
                            ];
                            @endphp
                            <div class="{{ $colors[$status] ?? 'bg-blue-600' }} h-3 rounded-full" style="width: {{ ($count / $summary['total_services']) * 100 }}%"></div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow">
            <div class="p-6 border-b">
                <h2 class="text-lg font-semibold text-gray-900">Services by Billing Cycle</h2>
            </div>
            <div class="p-6">
                <div class="space-y-4">
                    @forelse($billingBreakdown as $billing)
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <span class="font-semibold text-gray-900">{{ ucfirst(str_replace('_', ' ', $billing->billing_cycle)) }}</span>
                            <div class="text-right">
                                <span class="text-lg font-bold">{{ $billing->count }}</span>
                                <p class="text-xs text-gray-500">MRR: ${{ number_format($billing->mrr, 2) }}</p>
                            </div>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-3">
                            <div class="bg-purple-600 h-3 rounded-full" style="width: {{ ($billing->count / $summary['active_services']) * 100 }}%"></div>
                        </div>
                    </div>
                    @empty
                    <p class="text-gray-500">No active services</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
