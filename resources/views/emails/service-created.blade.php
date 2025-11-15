<x-mail::message>
# Your Service is Ready!

Hello {{ $client->user->name }},

Congratulations! Your new service has been successfully created and is now ready to use.

<x-mail::panel>
**Service:** {{ $product->name }}
**Status:** {{ ucfirst($service->status) }}
@if($service->domain)
**Domain:** {{ $service->domain }}
@endif
@if($service->username)
**Username:** {{ $service->username }}
@endif
**Billing Cycle:** {{ ucfirst(str_replace('_', ' ', $service->billing_cycle)) }}
**Next Due Date:** {{ $service->next_due_date->format('F d, Y') }}
</x-mail::panel>

## Service Details

**Product:** {{ $product->name }}
**Price:** ${{ number_format($service->price, 2) }} / {{ $service->billing_cycle }}

@if($service->status == 'active')
Your service is now active and ready to use!
@elseif($service->status == 'pending')
Your service is being set up and will be activated soon.
@endif

@if($service->server_details)
## Server/Access Details

@php
$details = is_string($service->server_details) ? json_decode($service->server_details, true) : $service->server_details;
@endphp

@foreach($details as $key => $value)
**{{ ucfirst(str_replace('_', ' ', $key)) }}:** {{ $value }}
@endforeach

<x-mail::button :url="route('client.services.show', $service)">
View Service Details
</x-mail::button>
@endif

## Next Steps

1. Log in to your client area to manage your service
2. Configure your service settings if needed
3. Contact support if you need any assistance

<x-mail::button :url="route('client.services.show', $service)">
Manage Service
</x-mail::button>

If you have any questions, our support team is here to help!

Thanks,
{{ config('app.name') }}
</x-mail::message>
