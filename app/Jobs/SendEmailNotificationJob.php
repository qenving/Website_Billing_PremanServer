<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendEmailNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public string $to;
    public string $mailableClass;
    public array $mailableParams;

    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 3;

    /**
     * The number of seconds to wait before retrying the job.
     */
    public int $backoff = 60;

    /**
     * Create a new job instance.
     */
    public function __construct(string $to, string $mailableClass, array $mailableParams = [])
    {
        $this->to = $to;
        $this->mailableClass = $mailableClass;
        $this->mailableParams = $mailableParams;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            // Validate mailable class exists
            if (!class_exists($this->mailableClass)) {
                \Log::error('Mailable class not found: ' . $this->mailableClass);
                return;
            }

            // Create mailable instance
            $mailable = new $this->mailableClass(...array_values($this->mailableParams));

            // Send email
            Mail::to($this->to)->send($mailable);

            \Log::info('Email sent successfully to ' . $this->to . ' using ' . $this->mailableClass);

        } catch (\Exception $e) {
            \Log::error('Failed to send email to ' . $this->to . ': ' . $e->getMessage());

            // Re-throw to allow queue retry mechanism
            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        \Log::error('Email job failed after all retries: ' . $exception->getMessage(), [
            'to' => $this->to,
            'mailable' => $this->mailableClass,
        ]);

        // You could send notification to admin here
    }
}
