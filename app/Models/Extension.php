<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Extension extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'type',
        'provider_class',
        'version',
        'author',
        'description',
        'is_enabled',
        'is_installed',
        'sort_order',
    ];

    protected $casts = [
        'is_enabled' => 'boolean',
        'is_installed' => 'boolean',
        'sort_order' => 'integer',
    ];

    // Relasi
    public function configs(): HasMany
    {
        return $this->hasMany(ExtensionConfig::class);
    }

    public function paymentExtension(): HasOne
    {
        return $this->hasOne(PaymentExtension::class);
    }

    public function provisioningExtension(): HasOne
    {
        return $this->hasOne(ProvisioningExtension::class);
    }

    // Helper methods
    public function getConfig(string $key, $default = null)
    {
        $config = $this->configs()->where('key', $key)->first();

        if (!$config) {
            return $default;
        }

        return $config->is_encrypted ? decrypt($config->value) : $config->value;
    }

    public function setConfig(string $key, $value, bool $encrypted = false): void
    {
        $this->configs()->updateOrCreate(
            ['key' => $key],
            [
                'value' => $encrypted ? encrypt($value) : $value,
                'is_encrypted' => $encrypted,
            ]
        );
    }

    public function getAllConfigs(): array
    {
        $configs = [];

        foreach ($this->configs as $config) {
            $configs[$config->key] = $config->is_encrypted
                ? decrypt($config->value)
                : $config->value;
        }

        return $configs;
    }

    public function isPaymentGateway(): bool
    {
        return $this->type === 'payment_gateway';
    }

    public function isProvisioningPanel(): bool
    {
        return $this->type === 'provisioning_panel';
    }

    // Scopes
    public function scopeEnabled($query)
    {
        return $query->where('is_enabled', true);
    }

    public function scopePaymentGateways($query)
    {
        return $query->where('type', 'payment_gateway');
    }

    public function scopeProvisioningPanels($query)
    {
        return $query->where('type', 'provisioning_panel');
    }
}
