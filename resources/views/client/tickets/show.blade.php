@extends('layouts.app-client')

@section('title', 'Ticket #' . $ticket->id . ' - ' . $ticket->subject)

@section('content')
<div class="container mx-auto px-4 py-8 max-w-5xl">
    <div class="mb-6">
        <div class="flex items-center justify-between mb-4">
            <div class="flex items-center">
                <a href="{{ route('client.tickets.index') }}" class="text-gray-600 hover:text-gray-900 mr-4">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                </a>
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Ticket #{{ $ticket->id }}</h1>
                    <p class="text-gray-600 mt-1">{{ $ticket->subject }}</p>
                </div>
            </div>
            @if($ticket->status != 'closed')
            <form method="POST" action="{{ route('client.tickets.close', $ticket) }}">
                @csrf
                <button type="submit" class="bg-gray-600 hover:bg-gray-700 text-white px-6 py-2 rounded-lg font-semibold transition duration-200">
                    Close Ticket
                </button>
            </form>
            @endif
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Ticket Content -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Original Ticket Message -->
            <div class="bg-white rounded-lg shadow">
                <div class="p-6 border-b">
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            <div class="w-12 h-12 rounded-full bg-blue-600 flex items-center justify-center text-white font-bold text-lg">
                                {{ strtoupper(substr($ticket->user->name, 0, 2)) }}
                            </div>
                        </div>
                        <div class="ml-4 flex-1">
                            <div class="flex items-center justify-between">
                                <h3 class="font-semibold text-gray-900">{{ $ticket->user->name }}</h3>
                                <span class="text-sm text-gray-500">{{ $ticket->created_at->format('M d, Y H:i') }}</span>
                            </div>
                            <p class="text-sm text-gray-500">Customer</p>
                        </div>
                    </div>
                </div>
                <div class="p-6">
                    <div class="prose max-w-none text-gray-700">
                        {!! nl2br(e($ticket->message)) !!}
                    </div>
                    @if($ticket->attachments && count($ticket->attachments) > 0)
                    <div class="mt-6 pt-6 border-t">
                        <h4 class="text-sm font-semibold text-gray-900 mb-3">Attachments:</h4>
                        <div class="space-y-2">
                            @foreach($ticket->attachments as $attachment)
                            <a href="{{ Storage::url($attachment->file_path) }}" target="_blank" class="flex items-center text-blue-600 hover:underline text-sm">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/>
                                </svg>
                                {{ $attachment->filename }} ({{ $attachment->file_size }})
                            </a>
                            @endforeach
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Replies -->
            @forelse($ticket->replies ?? [] as $reply)
            <div class="bg-white rounded-lg shadow">
                <div class="p-6 border-b {{ $reply->is_staff ? 'bg-green-50' : 'bg-gray-50' }}">
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            <div class="w-12 h-12 rounded-full {{ $reply->is_staff ? 'bg-green-600' : 'bg-blue-600' }} flex items-center justify-center text-white font-bold text-lg">
                                {{ strtoupper(substr($reply->user->name, 0, 2)) }}
                            </div>
                        </div>
                        <div class="ml-4 flex-1">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center">
                                    <h3 class="font-semibold text-gray-900">{{ $reply->user->name }}</h3>
                                    @if($reply->is_staff)
                                    <span class="ml-2 px-2 py-1 bg-green-100 text-green-800 rounded text-xs font-semibold">Support Team</span>
                                    @endif
                                </div>
                                <span class="text-sm text-gray-500">{{ $reply->created_at->format('M d, Y H:i') }}</span>
                            </div>
                            <p class="text-sm text-gray-500">{{ $reply->is_staff ? 'Support Staff' : 'Customer' }}</p>
                        </div>
                    </div>
                </div>
                <div class="p-6">
                    <div class="prose max-w-none text-gray-700">
                        {!! nl2br(e($reply->message)) !!}
                    </div>
                    @if($reply->attachments && count($reply->attachments) > 0)
                    <div class="mt-4 pt-4 border-t">
                        <h5 class="text-sm font-semibold text-gray-900 mb-2">Attachments:</h5>
                        <div class="space-y-1">
                            @foreach($reply->attachments as $attachment)
                            <a href="{{ Storage::url($attachment->file_path) }}" target="_blank" class="flex items-center text-blue-600 hover:underline text-sm">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/>
                                </svg>
                                {{ $attachment->filename }}
                            </a>
                            @endforeach
                        </div>
                    </div>
                    @endif
                </div>
            </div>
            @empty
            <div class="bg-gray-50 rounded-lg p-8 text-center">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                </svg>
                <p class="mt-2 text-gray-500">No replies yet</p>
                <p class="text-sm text-gray-400">Our support team will respond soon</p>
            </div>
            @endforelse

            <!-- Reply Form -->
            @if($ticket->status != 'closed')
            <div class="bg-white rounded-lg shadow">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Add Reply</h3>
                    <form method="POST" action="{{ route('client.tickets.reply', $ticket) }}" enctype="multipart/form-data">
                        @csrf
                        <div class="space-y-4">
                            <div>
                                <label for="message" class="block text-sm font-medium text-gray-700 mb-2">Your Reply</label>
                                <textarea name="message"
                                          id="message"
                                          rows="6"
                                          required
                                          class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('message') border-red-500 @enderror"
                                          placeholder="Type your reply here...">{{ old('message') }}</textarea>
                                @error('message')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label for="attachments" class="block text-sm font-medium text-gray-700 mb-2">Attachments (Optional)</label>
                                <input type="file"
                                       name="attachments[]"
                                       id="attachments"
                                       multiple
                                       accept="image/*,.pdf,.doc,.docx,.txt,.zip"
                                       class="w-full px-4 py-2 border rounded-lg">
                                <p class="mt-1 text-xs text-gray-500">Max 5MB per file</p>
                            </div>
                            <div class="flex justify-end">
                                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg font-semibold transition duration-200">
                                    Send Reply
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            @else
            <div class="bg-gray-50 border border-gray-200 rounded-lg p-6 text-center">
                <p class="text-gray-600">This ticket is closed and cannot receive new replies.</p>
                <a href="{{ route('client.tickets.create') }}" class="mt-4 inline-block text-blue-600 hover:underline">Create a new ticket</a>
            </div>
            @endif
        </div>

        <!-- Sidebar -->
        <div class="lg:col-span-1 space-y-6">
            <!-- Ticket Information -->
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Ticket Information</h3>
                <dl class="space-y-3">
                    <div>
                        <dt class="text-sm text-gray-500">Status</dt>
                        @php
                        $statusColors = [
                            'open' => 'bg-blue-100 text-blue-800',
                            'in_progress' => 'bg-yellow-100 text-yellow-800',
                            'waiting_customer' => 'bg-purple-100 text-purple-800',
                            'closed' => 'bg-green-100 text-green-800'
                        ];
                        @endphp
                        <dd class="mt-1">
                            <span class="px-3 py-1 rounded-full text-xs font-semibold {{ $statusColors[$ticket->status] ?? 'bg-gray-100 text-gray-800' }}">
                                {{ ucwords(str_replace('_', ' ', $ticket->status)) }}
                            </span>
                        </dd>
                    </div>
                    <div>
                        <dt class="text-sm text-gray-500">Priority</dt>
                        @php
                        $priorityColors = [
                            'low' => 'bg-gray-100 text-gray-800',
                            'medium' => 'bg-blue-100 text-blue-800',
                            'high' => 'bg-orange-100 text-orange-800',
                            'urgent' => 'bg-red-100 text-red-800'
                        ];
                        @endphp
                        <dd class="mt-1">
                            <span class="px-3 py-1 rounded-full text-xs font-semibold {{ $priorityColors[$ticket->priority] ?? 'bg-gray-100 text-gray-800' }}">
                                {{ ucfirst($ticket->priority) }}
                            </span>
                        </dd>
                    </div>
                    <div>
                        <dt class="text-sm text-gray-500">Department</dt>
                        <dd class="mt-1 font-medium text-gray-900">{{ $ticket->department->name ?? 'General' }}</dd>
                    </div>
                    @if($ticket->service)
                    <div>
                        <dt class="text-sm text-gray-500">Related Service</dt>
                        <dd class="mt-1">
                            <a href="{{ route('client.services.show', $ticket->service) }}" class="text-blue-600 hover:underline font-medium">
                                {{ $ticket->service->product->name ?? 'Service' }}
                            </a>
                        </dd>
                    </div>
                    @endif
                    <div>
                        <dt class="text-sm text-gray-500">Created</dt>
                        <dd class="mt-1 font-medium text-gray-900">{{ $ticket->created_at->format('M d, Y H:i') }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm text-gray-500">Last Updated</dt>
                        <dd class="mt-1 font-medium text-gray-900">{{ $ticket->updated_at->diffForHumans() }}</dd>
                    </div>
                    @if($ticket->last_reply_at)
                    <div>
                        <dt class="text-sm text-gray-500">Last Reply</dt>
                        <dd class="mt-1 font-medium text-gray-900">{{ $ticket->last_reply_at->diffForHumans() }}</dd>
                    </div>
                    @endif
                </dl>
            </div>

            <!-- Quick Actions -->
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Quick Actions</h3>
                <div class="space-y-2">
                    @if($ticket->status != 'closed')
                    <form method="POST" action="{{ route('client.tickets.close', $ticket) }}">
                        @csrf
                        <button type="submit" class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 rounded-lg transition">
                            Close Ticket
                        </button>
                    </form>
                    @endif
                    @if($ticket->service)
                    <a href="{{ route('client.services.show', $ticket->service) }}" class="block px-4 py-2 text-sm text-blue-700 hover:bg-blue-50 rounded-lg transition">
                        View Related Service
                    </a>
                    @endif
                    <a href="{{ route('client.tickets.create') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 rounded-lg transition">
                        Create New Ticket
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
