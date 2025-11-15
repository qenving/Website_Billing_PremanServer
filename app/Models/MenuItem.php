<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MenuItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'parent_id',
        'position',
        'label',
        'url',
        'icon',
        'target',
        'visibility',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'sort_order' => 'integer',
        'is_active' => 'boolean',
    ];

    // Relasi
    public function parent(): BelongsTo
    {
        return $this->belongsTo(MenuItem::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(MenuItem::class, 'parent_id')->orderBy('sort_order');
    }

    // Helper methods
    public function isVisible(bool $isAuthenticated = false): bool
    {
        if (!$this->is_active) {
            return false;
        }

        return match ($this->visibility) {
            'always' => true,
            'authenticated' => $isAuthenticated,
            'guest' => !$isAuthenticated,
            default => true,
        };
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeForPosition($query, string $position)
    {
        return $query->where('position', $position);
    }

    public function scopeRootItems($query)
    {
        return $query->whereNull('parent_id');
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order');
    }

    // Get menu tree
    public static function getMenuTree(string $position, bool $isAuthenticated = false): array
    {
        $items = static::active()
            ->forPosition($position)
            ->rootItems()
            ->ordered()
            ->with('children')
            ->get();

        return $items->filter(function ($item) use ($isAuthenticated) {
            return $item->isVisible($isAuthenticated);
        })->toArray();
    }
}
