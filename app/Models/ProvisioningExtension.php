<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProvisioningExtension extends Model
{
    use HasFactory;

    protected $fillable = [
        'extension_id',
        'api_endpoint',
        'api_version',
        'last_health_check_at',
        'health_status',
        'health_message',
        'capabilities',
    ];

    protected $casts = [
        'last_health_check_at' => 'datetime',
        'capabilities' => 'array',
    ];

    // Relasi
    public function extension(): BelongsTo
    {
        return $this->belongsTo(Extension::class);
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function services(): HasMany
    {
        return $this->hasMany(Service::class);
    }

    // Helper methods
    public function hasCapability(string $capability): bool
    {
        return in_array($capability, $this->capabilities ?? []);
    }

    public function isHealthy(): bool
    {
        return $this->health_status === 'ok';
    }

    public function updateHealthStatus(string $status, ?string $message = null): void
    {
        $this->update([
            'health_status' => $status,
            'health_message' => $message,
            'last_health_check_at' => now(),
        ]);
    }
}
