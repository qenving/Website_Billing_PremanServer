<?php

namespace App\Listeners;

use App\Events\UserRegisteredEvent;
use App\Mail\WelcomeMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendWelcomeEmail implements ShouldQueue
{
    public function handle(UserRegisteredEvent $event): void
    {
        // Send welcome email
        Mail::to($event->user->email)
            ->send(new WelcomeMail($event->user, $event->temporaryPassword));

        // Log activity
        \App\Models\ActivityLog::log(
            'user_registered',
            'New user registered: ' . $event->user->name,
            $event->user
        );
    }
}
