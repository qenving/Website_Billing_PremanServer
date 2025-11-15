<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TicketReply extends Model
{
    use HasFactory;

    protected $fillable = [
        'ticket_id',
        'user_id',
        'message',
        'attachments',
        'is_staff_reply',
    ];

    protected $casts = [
        'attachments' => 'array',
        'is_staff_reply' => 'boolean',
    ];

    // Relasi
    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Boot method untuk auto-update ticket status
    protected static function boot()
    {
        parent::boot();

        static::created(function ($reply) {
            $ticket = $reply->ticket;

            if ($reply->is_staff_reply) {
                $ticket->updateStatus('answered');
            } else {
                $ticket->updateStatus('customer_reply');
            }
        });
    }
}
