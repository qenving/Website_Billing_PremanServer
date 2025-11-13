<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Invoice extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'client_id',
        'invoice_number',
        'status',
        'subtotal',
        'tax_rate',
        'tax_amount',
        'total',
        'currency',
        'due_date',
        'paid_at',
        'payment_method',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'tax_rate' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'total' => 'decimal:2',
        'due_date' => 'date',
        'paid_at' => 'datetime',
    ];

    // Relasi
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(InvoiceItem::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    // Helper methods
    public function isPaid(): bool
    {
        return $this->status === 'paid';
    }

    public function isUnpaid(): bool
    {
        return $this->status === 'unpaid';
    }

    public function isOverdue(): bool
    {
        return $this->status === 'overdue' || ($this->isUnpaid() && $this->due_date < now());
    }

    public function markAsPaid(string $paymentMethod): void
    {
        $this->update([
            'status' => 'paid',
            'paid_at' => now(),
            'payment_method' => $paymentMethod,
        ]);
    }

    public function markAsOverdue(): void
    {
        if ($this->isUnpaid() && $this->due_date < now()) {
            $this->update(['status' => 'overdue']);
        }
    }

    public function calculateTotals(): void
    {
        $subtotal = $this->items()->sum('amount');
        $taxAmount = ($subtotal * $this->tax_rate) / 100;
        $total = $subtotal + $taxAmount;

        $this->update([
            'subtotal' => $subtotal,
            'tax_amount' => $taxAmount,
            'total' => $total,
        ]);
    }

    // Scopes
    public function scopeUnpaid($query)
    {
        return $query->whereIn('status', ['unpaid', 'overdue']);
    }

    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }

    public function scopeOverdue($query)
    {
        return $query->where('status', 'overdue')
            ->orWhere(function ($q) {
                $q->where('status', 'unpaid')
                    ->where('due_date', '<', now());
            });
    }
}
