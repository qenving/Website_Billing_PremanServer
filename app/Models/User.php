<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes, HasApiTokens;

    protected $fillable = [
        'name',
        'email',
        'password',
        'language',
        'role_id',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'two_factor_confirmed_at',
        'last_login_at',
        'last_login_ip',
        'is_active',
        'is_owner',
        'email_verified_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_secret',
        'two_factor_recovery_codes',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'two_factor_confirmed_at' => 'datetime',
        'last_login_at' => 'datetime',
        'is_active' => 'boolean',
        'is_owner' => 'boolean',
        'password' => 'hashed',
    ];

    // Relasi
    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    public function client(): HasOne
    {
        return $this->hasOne(Client::class);
    }

    public function auditLogs(): HasMany
    {
        return $this->hasMany(AuditLog::class);
    }

    public function ticketReplies(): HasMany
    {
        return $this->hasMany(TicketReply::class);
    }

    public function securitySettings(): HasOne
    {
        return $this->hasOne(SecuritySettings::class);
    }

    // Helper methods
    public function isAdmin(): bool
    {
        return in_array($this->role->name, ['super_admin', 'billing_admin', 'support', 'owner']);
    }

    public function isClient(): bool
    {
        return $this->role->name === 'client';
    }

    public function isOwner(): bool
    {
        return $this->is_owner === true;
    }

    public function hasPermission(string $permission): bool
    {
        return $this->role->hasPermission($permission);
    }

    public function hasTwoFactorEnabled(): bool
    {
        return !is_null($this->two_factor_secret) && !is_null($this->two_factor_confirmed_at);
    }

    /**
     * Boot method to add model protections
     */
    protected static function boot()
    {
        parent::boot();

        // Prevent OWNER from being deleted
        static::deleting(function ($user) {
            if ($user->isOwner()) {
                throw new \Exception('Cannot delete OWNER account. This account is protected.');
            }
        });

        // Prevent OWNER role from being changed
        static::updating(function ($user) {
            if ($user->isOwner() && $user->isDirty('role_id')) {
                throw new \Exception('Cannot change OWNER role. This account is protected.');
            }

            // Prevent is_owner flag from being removed
            if ($user->getOriginal('is_owner') === true && $user->is_owner === false) {
                throw new \Exception('Cannot remove OWNER status. This account is protected.');
            }
        });
    }
}
