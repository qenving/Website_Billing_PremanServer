<?php

namespace App\Listeners;

use App\Events\ServiceProvisionedEvent;
use App\Mail\ServiceCreatedMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendServiceProvisionedNotification implements ShouldQueue
{
    public function handle(ServiceProvisionedEvent $event): void
    {
        // Send service created email
        Mail::to($event->service->client->user->email)
            ->send(new ServiceCreatedMail($event->service));

        // Log activity
        \App\Models\ActivityLog::log(
            'service_provisioned',
            'Service provisioned: ' . $event->service->product->name,
            $event->service
        );
    }
}
