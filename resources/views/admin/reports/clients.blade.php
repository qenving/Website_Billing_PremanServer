@extends('layouts.app-admin')

@section('title', 'Clients Report')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Clients Report</h1>
            <p class="text-gray-600 mt-1">Client acquisition and growth metrics</p>
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
        </form>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
        <div class="bg-white rounded-lg shadow p-6">
            <p class="text-gray-500 text-sm">Total Clients</p>
            <p class="text-3xl font-bold text-gray-900 mt-2">{{ $summary['total_clients'] }}</p>
            <p class="text-xs text-gray-500 mt-1">All time</p>
        </div>
        <div class="bg-white rounded-lg shadow p-6">
            <p class="text-gray-500 text-sm">Active Clients</p>
            <p class="text-3xl font-bold text-green-600 mt-2">{{ $summary['active_clients'] }}</p>
            <p class="text-xs text-gray-500 mt-1">With active services</p>
        </div>
        <div class="bg-white rounded-lg shadow p-6">
            <p class="text-gray-500 text-sm">New This Month</p>
            <p class="text-3xl font-bold text-blue-600 mt-2">{{ $summary['new_this_month'] }}</p>
            <p class="text-xs text-gray-500 mt-1">{{ now()->format('F Y') }}</p>
        </div>
    </div>

    <!-- Top Clients by Revenue -->
    <div class="bg-white rounded-lg shadow mb-6">
        <div class="p-6 border-b">
            <h2 class="text-lg font-semibold text-gray-900">Top 10 Clients by Revenue</h2>
        </div>
        <div class="p-6">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Rank</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Client</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Email</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total Revenue</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Member Since</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($topClients as $index => $client)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    @if($index < 3)
                                    <span class="flex items-center justify-center w-8 h-8 rounded-full {{ $index === 0 ? 'bg-yellow-100 text-yellow-800' : ($index === 1 ? 'bg-gray-100 text-gray-800' : 'bg-orange-100 text-orange-800') }} font-bold text-sm">
                                        {{ $index + 1 }}
                                    </span>
                                    @else
                                    <span class="flex items-center justify-center w-8 h-8 text-gray-600 font-semibold">
                                        {{ $index + 1 }}
                                    </span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">{{ $client->user->name ?? 'N/A' }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-600">{{ $client->user->email ?? 'N/A' }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-bold text-green-600">${{ number_format($client->invoices_sum_total ?? 0, 2) }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-600">{{ $client->created_at->format('M d, Y') }}</div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="px-6 py-4 text-center text-gray-500">No client data available</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- New Clients Trend -->
    <div class="bg-white rounded-lg shadow">
        <div class="p-6 border-b">
            <h2 class="text-lg font-semibold text-gray-900">New Client Acquisitions</h2>
        </div>
        <div class="p-6">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">New Clients</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Growth</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($newClients as $day)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $day->date }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-blue-600">{{ $day->count }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <div class="w-full bg-gray-200 rounded-full h-2">
                                    <div class="bg-blue-600 h-2 rounded-full" style="width: {{ ($day->count / max($newClients->max('count'), 1)) * 100 }}%"></div>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="3" class="px-6 py-4 text-center text-gray-500">No new clients in this period</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Client Insights -->
    <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-lg shadow p-6 text-white">
            <h3 class="text-lg font-semibold mb-2">Client Retention</h3>
            <p class="text-3xl font-bold">{{ number_format(($summary['active_clients'] / max($summary['total_clients'], 1)) * 100, 1) }}%</p>
            <p class="text-sm text-blue-100 mt-1">Clients with active services</p>
        </div>
        <div class="bg-gradient-to-br from-green-500 to-green-600 rounded-lg shadow p-6 text-white">
            <h3 class="text-lg font-semibold mb-2">Growth Rate</h3>
            <p class="text-3xl font-bold">+{{ $summary['new_this_month'] }}</p>
            <p class="text-sm text-green-100 mt-1">New clients this month</p>
        </div>
    </div>
</div>
@endsection
