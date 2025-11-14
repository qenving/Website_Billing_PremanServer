<x-mail::message>
# Welcome to {{ config('app.name') }}!

Hello {{ $user->name }},

Welcome! We're excited to have you as a new member of our community.

Your account has been successfully created and you can now access all of our services.

<x-mail::panel>
**Email:** {{ $user->email }}
@if($password)
**Temporary Password:** {{ $password }}

**Important:** Please change your password after your first login for security.
@endif
</x-mail::panel>

## Getting Started

Here's what you can do with your new account:

- **Browse Services:** Explore our wide range of products and services
- **Manage Account:** Update your profile and security settings
- **24/7 Support:** Get help from our expert support team anytime
- **Client Portal:** Access all your services from one dashboard

<x-mail::button :url="route('login')">
Access Your Account
</x-mail::button>

## Quick Links

- [Browse Products]({{ route('client.orders.index') }})
- [Client Dashboard]({{ route('client.dashboard') }})
- [Account Settings]({{ route('client.account.index') }})
- [Support Center]({{ route('client.tickets.index') }})

## Need Help?

Our support team is available 24/7 to assist you with any questions or concerns.

<x-mail::button :url="route('client.tickets.create')" color="success">
Contact Support
</x-mail::button>

We're here to help you succeed!

Thanks,
{{ config('app.name') }} Team
</x-mail::message>
