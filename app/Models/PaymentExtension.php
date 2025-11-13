<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PaymentExtension extends Model
{
    use HasFactory;

    protected $fillable = [
        'extension_id',
        'supports_currencies',
        'supports_refund',
        'supports_recurring',
        'fee_type',
        'fee_amount',
        'min_transaction',
        'max_transaction',
        'test_mode',
    ];

    protected $casts = [
        'supports_currencies' => 'array',
        'supports_refund' => 'boolean',
        'supports_recurring' => 'boolean',
        'fee_amount' => 'decimal:2',
        'min_transaction' => 'decimal:2',
        'max_transaction' => 'decimal:2',
        'test_mode' => 'boolean',
    ];

    // Relasi
    public function extension(): BelongsTo
    {
        return $this->belongsTo(Extension::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(PaymentTransaction::class);
    }

    // Helper methods
    public function supportsCurrency(string $currency): bool
    {
        if (empty($this->supports_currencies)) {
            return true; // jika kosong, support semua
        }

        return in_array(strtoupper($currency), $this->supports_currencies);
    }

    public function calculateFee(float $amount): float
    {
        if ($this->fee_type === 'fixed') {
            return $this->fee_amount;
        }

        if ($this->fee_type === 'percentage') {
            return ($amount * $this->fee_amount) / 100;
        }

        return 0;
    }

    public function isWithinTransactionLimits(float $amount): bool
    {
        if ($this->min_transaction && $amount < $this->min_transaction) {
            return false;
        }

        if ($this->max_transaction && $amount > $this->max_transaction) {
            return false;
        }

        return true;
    }
}
