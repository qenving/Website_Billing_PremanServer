<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ThemeSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'is_active',
        'style_name',
        'color_scheme',
        'primary_color',
        'secondary_color',
        'accent_color',
        'layout_type',
        'footer_style',
        'custom_css',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // Helper methods
    public static function getActive(): ?self
    {
        return static::where('is_active', true)->first();
    }

    public function activate(): void
    {
        // Deactivate semua theme lain
        static::query()->update(['is_active' => false]);

        // Activate theme ini
        $this->update(['is_active' => true]);
    }

    public function getCSSVariables(): array
    {
        return [
            '--color-primary' => $this->primary_color,
            '--color-secondary' => $this->secondary_color,
            '--color-accent' => $this->accent_color,
        ];
    }

    // Get color palette for specific style
    public static function getStylePreset(string $styleName): array
    {
        $presets = [
            'default' => [
                'primary' => '#3B82F6',
                'secondary' => '#10B981',
                'accent' => '#F59E0B',
            ],
            'modern' => [
                'primary' => '#8B5CF6',
                'secondary' => '#EC4899',
                'accent' => '#F59E0B',
            ],
            'depth' => [
                'primary' => '#0EA5E9',
                'secondary' => '#06B6D4',
                'accent' => '#14B8A6',
            ],
            'futuristic' => [
                'primary' => '#A855F7',
                'secondary' => '#6366F1',
                'accent' => '#EC4899',
            ],
        ];

        return $presets[$styleName] ?? $presets['default'];
    }
}
