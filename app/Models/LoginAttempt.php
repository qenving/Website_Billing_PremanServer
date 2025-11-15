<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LoginAttempt extends Model
{
    use HasFactory;

    protected $fillable = [
        'email',
        'ip_address',
        'success',
        'error_message',
        'attempted_at',
    ];

    protected $casts = [
        'success' => 'boolean',
        'attempted_at' => 'datetime',
    ];

    // Helper methods
    public static function recordAttempt(string $email, bool $success, ?string $errorMessage = null): void
    {
        static::create([
            'email' => $email,
            'ip_address' => request()->ip(),
            'success' => $success,
            'error_message' => $errorMessage,
            'attempted_at' => now(),
        ]);
    }

    public static function getRecentFailedAttempts(string $email, int $minutes = 15): int
    {
        return static::where('email', $email)
            ->where('success', false)
            ->where('attempted_at', '>=', now()->subMinutes($minutes))
            ->count();
    }

    public static function getRecentFailedAttemptsByIp(string $ip, int $minutes = 15): int
    {
        return static::where('ip_address', $ip)
            ->where('success', false)
            ->where('attempted_at', '>=', now()->subMinutes($minutes))
            ->count();
    }

    public static function isAccountLocked(string $email, int $maxAttempts = 5, int $lockoutMinutes = 15): bool
    {
        $failedAttempts = static::getRecentFailedAttempts($email, $lockoutMinutes);

        return $failedAttempts >= $maxAttempts;
    }

    public static function clearAttempts(string $email): void
    {
        static::where('email', $email)->delete();
    }

    // Scopes
    public function scopeFailed($query)
    {
        return $query->where('success', false);
    }

    public function scopeSuccessful($query)
    {
        return $query->where('success', true);
    }

    public function scopeRecent($query, int $minutes = 60)
    {
        return $query->where('attempted_at', '>=', now()->subMinutes($minutes));
    }
}
