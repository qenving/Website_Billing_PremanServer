<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Service extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'client_id',
        'product_id',
        'provisioning_extension_id',
        'service_number',
        'status',
        'provisioning_status',
        'provisioning_external_id',
        'provisioning_data',
        'price',
        'currency',
        'billing_cycle',
        'next_due_date',
        'registered_at',
        'terminated_at',
    ];

    protected $casts = [
        'provisioning_data' => 'array',
        'price' => 'decimal:2',
        'next_due_date' => 'date',
        'registered_at' => 'datetime',
        'terminated_at' => 'datetime',
    ];

    // Relasi
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function provisioningExtension(): BelongsTo
    {
        return $this->belongsTo(ProvisioningExtension::class);
    }

    public function invoiceItems(): HasMany
    {
        return $this->hasMany(InvoiceItem::class);
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

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isSuspended(): bool
    {
        return $this->status === 'suspended';
    }

    public function isTerminated(): bool
    {
        return $this->status === 'terminated';
    }

    public function isProvisioningCompleted(): bool
    {
        return $this->provisioning_status === 'completed';
    }

    public function getProvisioningData(string $key, $default = null)
    {
        return $this->provisioning_data[$key] ?? $default;
    }

    public function setProvisioningData(string $key, $value): void
    {
        $data = $this->provisioning_data ?? [];
        $data[$key] = $value;
        $this->provisioning_data = $data;
        $this->save();
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeOverdue($query)
    {
        return $query->where('next_due_date', '<', now())
            ->where('status', 'active');
    }
}
