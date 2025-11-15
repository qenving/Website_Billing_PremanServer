<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Role extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'display_name',
        'description',
        'permissions',
        'is_system',
        'is_protected',
    ];

    protected $casts = [
        'permissions' => 'array',
        'is_system' => 'boolean',
        'is_protected' => 'boolean',
    ];

    // Relasi
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    // Helper methods
    public function hasPermission(string $permission): bool
    {
        return in_array($permission, $this->permissions ?? []);
    }

    public function isSuperAdmin(): bool
    {
        return $this->name === 'super_admin';
    }

    public function isOwner(): bool
    {
        return $this->slug === 'owner';
    }

    public function isProtected(): bool
    {
        return $this->is_protected === true;
    }

    /**
     * Boot method to add role protections
     */
    protected static function boot()
    {
        parent::boot();

        // Prevent protected roles from being deleted
        static::deleting(function ($role) {
            if ($role->isProtected()) {
                throw new \Exception("Cannot delete protected role '{$role->name}'. This role is system-protected.");
            }
        });

        // Prevent certain attributes of protected roles from being modified
        static::updating(function ($role) {
            if ($role->isProtected()) {
                // Prevent changing slug of protected roles
                if ($role->isDirty('slug')) {
                    throw new \Exception("Cannot modify slug of protected role '{$role->name}'.");
                }

                // Prevent removing protected status
                if ($role->getOriginal('is_protected') === true && $role->is_protected === false) {
                    throw new \Exception("Cannot remove protected status from role '{$role->name}'.");
                }
            }
        });
    }
}
