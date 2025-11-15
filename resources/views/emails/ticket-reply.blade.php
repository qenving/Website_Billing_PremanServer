<x-mail::message>
# New Reply to Your Support Ticket

Hello {{ $ticket->user->name }},

A new reply has been added to your support ticket.

<x-mail::panel>
**Ticket #{{ $ticket->id }}:** {{ $ticket->subject }}
**Status:** {{ ucfirst(str_replace('_', ' ', $ticket->status)) }}
**Priority:** {{ ucfirst($ticket->priority) }}
**Replied by:** {{ $repliedBy->name }} @if($reply->is_staff)(Support Team)@endif
</x-mail::panel>

## Reply Message

{{ $reply->message }}

---

<x-mail::button :url="route('client.tickets.show', $ticket)">
View Ticket & Reply
</x-mail::button>

@if($ticket->status == 'waiting_customer')
**Action Required:** This ticket is awaiting your response. Please reply at your earliest convenience.
@endif

## Quick Actions

- [Reply to this ticket]({{ route('client.tickets.show', $ticket) }})
- [View all tickets]({{ route('client.tickets.index') }})
- [Create new ticket]({{ route('client.tickets.create') }})

If you have already resolved this issue, you can close the ticket from your client area.

Thanks,
{{ config('app.name') }} Support Team
</x-mail::message>
