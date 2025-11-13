<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Client extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'company_name',
        'phone',
        'address',
        'city',
        'state',
        'country',
        'postal_code',
        'tax_id',
        'currency',
        'language',
        'total_spent',
        'status',
    ];

    protected $casts = [
        'total_spent' => 'decimal:2',
    ];

    // Relasi
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function services(): HasMany
    {
        return $this->hasMany(Service::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class);
    }

    // Helper methods
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isSuspended(): bool
    {
        return $this->status === 'suspended';
    }

    public function isBlocked(): bool
    {
        return $this->status === 'blocked';
    }

    public function getActiveServicesCount(): int
    {
        return $this->services()->where('status', 'active')->count();
    }

    public function getUnpaidInvoicesCount(): int
    {
        return $this->invoices()->whereIn('status', ['unpaid', 'overdue'])->count();
    }

    public function getOpenTicketsCount(): int
    {
        return $this->tickets()->whereIn('status', ['open', 'customer_reply'])->count();
    }
}
