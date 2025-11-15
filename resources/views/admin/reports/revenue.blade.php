@extends('layouts.app-admin')

@section('title', 'Revenue Report')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Revenue Report</h1>
            <p class="text-gray-600 mt-1">Financial performance and trends</p>
        </div>
        <a href="{{ route('admin.reports.index') }}" class="text-blue-600 hover:text-blue-700">
            ← Back to Reports
        </a>
    </div>

    <!-- Date Filter -->
    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <form method="GET" class="flex flex-wrap gap-4 items-end">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Start Date</label>
                <input type="date" name="start_date" value="{{ request('start_date', $startDate) }}" class="px-4 py-2 border rounded-lg">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">End Date</label>
                <input type="date" name="end_date" value="{{ request('end_date', $endDate) }}" class="px-4 py-2 border rounded-lg">
            </div>
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg font-semibold">
                Filter
            </button>
            <a href="{{ route('admin.reports.export', ['type' => 'revenue', 'start_date' => $startDate, 'end_date' => $endDate]) }}" class="bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded-lg font-semibold">
                Export CSV
            </a>
        </form>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
        <div class="bg-white rounded-lg shadow p-6">
            <p class="text-gray-500 text-sm">Total Revenue</p>
            <p class="text-3xl font-bold text-green-600 mt-2">${{ number_format($summary['total_revenue'], 2) }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-6">
            <p class="text-gray-500 text-sm">Total Payments</p>
            <p class="text-3xl font-bold text-blue-600 mt-2">{{ $summary['total_payments'] }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-6">
            <p class="text-gray-500 text-sm">Average Payment</p>
            <p class="text-3xl font-bold text-purple-600 mt-2">${{ number_format($summary['avg_payment'], 2) }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-6">
            <p class="text-gray-500 text-sm">Pending Amount</p>
            <p class="text-3xl font-bold text-yellow-600 mt-2">${{ number_format($summary['pending_amount'], 2) }}</p>
        </div>
    </div>

    <!-- Daily Revenue Chart -->
    <div class="bg-white rounded-lg shadow mb-6">
        <div class="p-6 border-b">
            <h2 class="text-lg font-semibold text-gray-900">Daily Revenue</h2>
        </div>
        <div class="p-6">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Revenue</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Trend</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($dailyRevenue as $day)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $day->date }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-green-600">${{ number_format($day->total, 2) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <div class="w-full bg-gray-200 rounded-full h-2">
                                    <div class="bg-green-600 h-2 rounded-full" style="width: {{ min(($day->total / $summary['total_revenue']) * 100, 100) }}%"></div>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="3" class="px-6 py-4 text-center text-gray-500">No revenue data for this period</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Revenue by Gateway -->
    <div class="bg-white rounded-lg shadow">
        <div class="p-6 border-b">
            <h2 class="text-lg font-semibold text-gray-900">Revenue by Payment Gateway</h2>
        </div>
        <div class="p-6">
            <div class="space-y-4">
                @forelse($revenueByGateway as $gateway)
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <div class="flex items-center">
                            <span class="font-semibold text-gray-900">{{ ucfirst($gateway->gateway) }}</span>
                            <span class="ml-2 text-sm text-gray-500">({{ $gateway->count }} payments)</span>
                        </div>
                        <span class="text-lg font-bold text-green-600">${{ number_format($gateway->total, 2) }}</span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-3">
                        <div class="bg-blue-600 h-3 rounded-full" style="width: {{ ($gateway->total / $summary['total_revenue']) * 100 }}%"></div>
                    </div>
                    <p class="text-xs text-gray-500 mt-1">{{ number_format(($gateway->total / $summary['total_revenue']) * 100, 1) }}% of total revenue</p>
                </div>
                @empty
                <p class="text-gray-500">No payment gateway data available</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
