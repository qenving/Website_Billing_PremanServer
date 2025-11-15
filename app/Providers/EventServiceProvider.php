<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use App\Events\InvoicePaidEvent;
use App\Events\ServiceProvisionedEvent;
use App\Events\UserRegisteredEvent;
use App\Listeners\SendInvoicePaidNotification;
use App\Listeners\SendServiceProvisionedNotification;
use App\Listeners\SendWelcomeEmail;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        InvoicePaidEvent::class => [
            SendInvoicePaidNotification::class,
        ],
        ServiceProvisionedEvent::class => [
            SendServiceProvisionedNotification::class,
        ],
        UserRegisteredEvent::class => [
            SendWelcomeEmail::class,
        ],
    ];

    /**
     * Register any events for your application.
     */
    public function boot(): void
    {
        //
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     */
    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}
