<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_id',
        'client_id',
        'payment_extension_id',
        'transaction_id',
        'payment_number',
        'amount',
        'currency',
        'status',
        'payment_method',
        'gateway_fee',
        'gateway_response',
        'paid_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'gateway_fee' => 'decimal:2',
        'gateway_response' => 'array',
        'paid_at' => 'datetime',
    ];

    // Relasi
    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function paymentExtension(): BelongsTo
    {
        return $this->belongsTo(PaymentExtension::class);
    }

    // Helper methods
    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }

    public function isRefunded(): bool
    {
        return $this->status === 'refunded';
    }

    public function markAsCompleted(): void
    {
        $this->update([
            'status' => 'completed',
            'paid_at' => now(),
        ]);

        // Update invoice status
        if ($this->invoice) {
            $this->invoice->markAsPaid($this->payment_method);
        }

        // Update client total spent
        if ($this->client) {
            $this->client->increment('total_spent', $this->amount);
        }
    }

    public function markAsFailed(string $reason = null): void
    {
        $gatewayResponse = $this->gateway_response ?? [];
        if ($reason) {
            $gatewayResponse['failure_reason'] = $reason;
        }

        $this->update([
            'status' => 'failed',
            'gateway_response' => $gatewayResponse,
        ]);
    }

    // Scopes
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }
}
