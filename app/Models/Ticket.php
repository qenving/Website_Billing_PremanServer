<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Ticket extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'client_id',
        'ticket_number',
        'department_id',
        'service_id',
        'subject',
        'status',
        'priority',
        'last_reply_at',
        'closed_at',
    ];

    protected $casts = [
        'last_reply_at' => 'datetime',
        'closed_at' => 'datetime',
    ];

    // Relasi
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(TicketDepartment::class, 'department_id');
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function replies(): HasMany
    {
        return $this->hasMany(TicketReply::class);
    }

    // Helper methods
    public function isOpen(): bool
    {
        return in_array($this->status, ['open', 'answered', 'customer_reply', 'on_hold']);
    }

    public function isClosed(): bool
    {
        return $this->status === 'closed';
    }

    public function close(): void
    {
        $this->update([
            'status' => 'closed',
            'closed_at' => now(),
        ]);
    }

    public function reopen(): void
    {
        $this->update([
            'status' => 'open',
            'closed_at' => null,
        ]);
    }

    public function updateStatus(string $status): void
    {
        $this->update([
            'status' => $status,
            'last_reply_at' => now(),
        ]);
    }

    // Scopes
    public function scopeOpen($query)
    {
        return $query->whereIn('status', ['open', 'answered', 'customer_reply', 'on_hold']);
    }

    public function scopeClosed($query)
    {
        return $query->where('status', 'closed');
    }

    public function scopeByPriority($query, string $priority)
    {
        return $query->where('priority', $priority);
    }
}
