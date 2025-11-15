<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'payment_extension_id',
        'payment_id',
        'transaction_reference',
        'type',
        'status',
        'raw_payload',
        'ip_address',
        'processed_at',
    ];

    protected $casts = [
        'processed_at' => 'datetime',
    ];

    // Relasi
    public function paymentExtension(): BelongsTo
    {
        return $this->belongsTo(PaymentExtension::class);
    }

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }

    // Helper methods
    public function markAsProcessed(): void
    {
        $this->update(['processed_at' => now()]);
    }

    public function isProcessed(): bool
    {
        return !is_null($this->processed_at);
    }
}
