<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('theme_settings', function (Blueprint $table) {
            $table->id();
            $table->string('name')->default('Default Theme');
            $table->boolean('is_active')->default(false);

            // Style presets
            $table->enum('style_name', ['default', 'modern', 'depth', 'futuristic'])->default('default');
            $table->enum('color_scheme', ['light', 'dark', 'auto'])->default('light');

            // Color customization (hex codes)
            $table->string('primary_color', 7)->default('#3B82F6');
            $table->string('secondary_color', 7)->default('#10B981');
            $table->string('accent_color', 7)->default('#F59E0B');

            // Layout settings
            $table->enum('layout_type', ['top_nav', 'left_sidebar'])->default('top_nav');
            $table->enum('footer_style', ['default', 'minimal'])->default('default');

            // Custom CSS
            $table->text('custom_css')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('theme_settings');
    }
};
