<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SecuritySettings extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'two_factor_enabled',
        'ip_whitelist',
        'session_timeout',
    ];

    protected $casts = [
        'two_factor_enabled' => 'boolean',
        'ip_whitelist' => 'array',
        'session_timeout' => 'integer',
    ];

    // Relasi
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Helper methods
    public function isIpWhitelisted(string $ip): bool
    {
        if (empty($this->ip_whitelist)) {
            return true; // jika whitelist kosong, allow semua IP
        }

        return in_array($ip, $this->ip_whitelist);
    }

    public function addIpToWhitelist(string $ip): void
    {
        $whitelist = $this->ip_whitelist ?? [];

        if (!in_array($ip, $whitelist)) {
            $whitelist[] = $ip;
            $this->update(['ip_whitelist' => $whitelist]);
        }
    }

    public function removeIpFromWhitelist(string $ip): void
    {
        $whitelist = $this->ip_whitelist ?? [];
        $whitelist = array_filter($whitelist, fn($item) => $item !== $ip);

        $this->update(['ip_whitelist' => array_values($whitelist)]);
    }
}
