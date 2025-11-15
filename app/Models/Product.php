<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'product_group_id',
        'name',
        'slug',
        'description',
        'type',
        'pricing_model',
        'price',
        'billing_cycle',
        'currency',
        'setup_fee',
        'stock_control',
        'stock_quantity',
        'provisioning_extension_id',
        'provisioning_config',
        'allowed_payment_extensions',
        'is_visible',
        'sort_order',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'setup_fee' => 'decimal:2',
        'stock_control' => 'boolean',
        'stock_quantity' => 'integer',
        'provisioning_config' => 'array',
        'allowed_payment_extensions' => 'array',
        'is_visible' => 'boolean',
        'sort_order' => 'integer',
    ];

    // Relasi
    public function productGroup(): BelongsTo
    {
        return $this->belongsTo(ProductGroup::class);
    }

    public function provisioningExtension(): BelongsTo
    {
        return $this->belongsTo(ProvisioningExtension::class);
    }

    public function services(): HasMany
    {
        return $this->hasMany(Service::class);
    }

    // Helper methods
    public function requiresProvisioning(): bool
    {
        return !is_null($this->provisioning_extension_id);
    }

    public function isInStock(): bool
    {
        if (!$this->stock_control) {
            return true;
        }

        return $this->stock_quantity > 0;
    }

    public function decrementStock(): void
    {
        if ($this->stock_control && $this->stock_quantity > 0) {
            $this->decrement('stock_quantity');
        }
    }

    public function isPaymentGatewayAllowed(int $extensionId): bool
    {
        // Jika null atau kosong, semua gateway diizinkan
        if (empty($this->allowed_payment_extensions)) {
            return true;
        }

        return in_array($extensionId, $this->allowed_payment_extensions);
    }

    public function getFormattedPrice(): string
    {
        return $this->currency . ' ' . number_format($this->price, 2);
    }

    // Scopes
    public function scopeVisible($query)
    {
        return $query->where('is_visible', true);
    }

    public function scopeInStock($query)
    {
        return $query->where(function ($q) {
            $q->where('stock_control', false)
                ->orWhere('stock_quantity', '>', 0);
        });
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type);
    }
}
