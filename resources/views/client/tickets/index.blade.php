@extends('layouts.app-client')

@section('title', 'Support Tickets')

@section('content')
<div class="container mx-auto px-4 py-8 max-w-6xl">
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Support Tickets</h1>
            <p class="text-gray-600 mt-1">View and manage your support requests</p>
        </div>
        <a href="{{ route('client.tickets.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg font-semibold transition duration-200">
            New Ticket
        </a>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
        <div class="bg-white rounded-lg shadow p-6">
            <p class="text-gray-500 text-sm">Total Tickets</p>
            <p class="text-3xl font-bold text-gray-900 mt-2">{{ $tickets->total() }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-6">
            <p class="text-gray-500 text-sm">Open</p>
            <p class="text-3xl font-bold text-blue-600 mt-2">{{ $stats['open'] ?? 0 }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-6">
            <p class="text-gray-500 text-sm">In Progress</p>
            <p class="text-3xl font-bold text-yellow-600 mt-2">{{ $stats['in_progress'] ?? 0 }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-6">
            <p class="text-gray-500 text-sm">Closed</p>
            <p class="text-3xl font-bold text-green-600 mt-2">{{ $stats['closed'] ?? 0 }}</p>
        </div>
    </div>

    <!-- Filter and Search -->
    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <form method="GET" class="flex flex-wrap gap-4">
            <div class="flex-1 min-w-[200px]">
                <input type="text" name="search" placeholder="Search by subject or ticket number..." value="{{ request('search') }}" class="w-full px-4 py-2 border rounded-lg">
            </div>
            <div>
                <select name="status" class="px-4 py-2 border rounded-lg">
                    <option value="">All Status</option>
                    <option value="open" {{ request('status') == 'open' ? 'selected' : '' }}>Open</option>
                    <option value="in_progress" {{ request('status') == 'in_progress' ? 'selected' : '' }}>In Progress</option>
                    <option value="waiting_customer" {{ request('status') == 'waiting_customer' ? 'selected' : '' }}>Waiting for Customer</option>
                    <option value="closed" {{ request('status') == 'closed' ? 'selected' : '' }}>Closed</option>
                </select>
            </div>
            <div>
                <select name="priority" class="px-4 py-2 border rounded-lg">
                    <option value="">All Priorities</option>
                    <option value="low" {{ request('priority') == 'low' ? 'selected' : '' }}>Low</option>
                    <option value="medium" {{ request('priority') == 'medium' ? 'selected' : '' }}>Medium</option>
                    <option value="high" {{ request('priority') == 'high' ? 'selected' : '' }}>High</option>
                    <option value="urgent" {{ request('priority') == 'urgent' ? 'selected' : '' }}>Urgent</option>
                </select>
            </div>
            <button type="submit" class="bg-gray-600 hover:bg-gray-700 text-white px-6 py-2 rounded-lg font-semibold">Filter</button>
            @if(request()->hasAny(['search', 'status', 'priority']))
            <a href="{{ route('client.tickets.index') }}" class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-6 py-2 rounded-lg font-semibold">Clear</a>
            @endif
        </form>
    </div>

    <!-- Tickets List -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        @forelse($tickets as $ticket)
        <a href="{{ route('client.tickets.show', $ticket) }}" class="block border-b last:border-b-0 hover:bg-gray-50 transition">
            <div class="p-6">
                <div class="flex items-start justify-between">
                    <div class="flex-1">
                        <div class="flex items-center mb-2">
                            <span class="text-sm text-gray-500 mr-3">#{{ $ticket->id }}</span>
                            <h3 class="text-lg font-semibold text-gray-900">{{ $ticket->subject }}</h3>
                            @if($ticket->unread_replies_count > 0)
                            <span class="ml-2 px-2 py-1 bg-red-100 text-red-800 rounded-full text-xs font-semibold">{{ $ticket->unread_replies_count }} new</span>
                            @endif
                        </div>
                        <p class="text-gray-600 text-sm mb-3 line-clamp-2">{{ Str::limit(strip_tags($ticket->message), 150) }}</p>
                        <div class="flex items-center text-sm text-gray-500 space-x-4">
                            <span>
                                <svg class="inline w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                {{ $ticket->created_at->diffForHumans() }}
                            </span>
                            @if($ticket->department)
                            <span>
                                <svg class="inline w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                </svg>
                                {{ $ticket->department->name ?? 'General' }}
                            </span>
                            @endif
                            @if($ticket->service)
                            <span>
                                <svg class="inline w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2m-2-4h.01M17 16h.01"/>
                                </svg>
                                {{ $ticket->service->product->name ?? 'Service' }}
                            </span>
                            @endif
                        </div>
                    </div>
                    <div class="ml-4 flex flex-col items-end space-y-2">
                        @php
                        $statusColors = [
                            'open' => 'bg-blue-100 text-blue-800',
                            'in_progress' => 'bg-yellow-100 text-yellow-800',
                            'waiting_customer' => 'bg-purple-100 text-purple-800',
                            'closed' => 'bg-green-100 text-green-800'
                        ];
                        $priorityColors = [
                            'low' => 'bg-gray-100 text-gray-800',
                            'medium' => 'bg-blue-100 text-blue-800',
                            'high' => 'bg-orange-100 text-orange-800',
                            'urgent' => 'bg-red-100 text-red-800'
                        ];
                        @endphp
                        <span class="px-3 py-1 rounded-full text-xs font-semibold {{ $statusColors[$ticket->status] ?? 'bg-gray-100 text-gray-800' }}">
                            {{ ucwords(str_replace('_', ' ', $ticket->status)) }}
                        </span>
                        <span class="px-3 py-1 rounded-full text-xs font-semibold {{ $priorityColors[$ticket->priority] ?? 'bg-gray-100 text-gray-800' }}">
                            {{ ucfirst($ticket->priority) }}
                        </span>
                        @if($ticket->last_reply_at)
                        <span class="text-xs text-gray-500">
                            Last reply: {{ $ticket->last_reply_at->diffForHumans() }}
                        </span>
                        @endif
                    </div>
                </div>
            </div>
        </a>
        @empty
        <div class="p-12 text-center">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"/>
            </svg>
            <p class="mt-4 text-gray-500 font-medium">No support tickets yet</p>
            <p class="mt-2 text-sm text-gray-400">Create a ticket to get help with your services</p>
            <a href="{{ route('client.tickets.create') }}" class="mt-6 inline-block bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg font-semibold">
                Create First Ticket
            </a>
        </div>
        @endforelse
    </div>

    <!-- Pagination -->
    @if($tickets->hasPages())
    <div class="mt-6">
        {{ $tickets->links() }}
    </div>
    @endif
</div>
@endsection
