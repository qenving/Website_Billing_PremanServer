<?php

namespace Database\Seeders;

use App\Models\ThemeSetting;
use Illuminate\Database\Seeder;

class ThemeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        ThemeSetting::updateOrCreate(
            ['id' => 1],
            [
                'style' => 'default',
                'color_scheme' => 'light',
                'primary_color' => '#3b82f6',
                'secondary_color' => '#10b981',
                'background_color' => '#ffffff',
                'surface_color' => '#f9fafb',
                'text_color' => '#111827',
                'layout_type' => 'left-sidebar',
                'custom_css' => '',
            ]
        );

        $this->command->info('✅ Theme settings created successfully');
    }
}
