<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('security_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->onDelete('cascade');
            $table->boolean('two_factor_enabled')->default(false);
            $table->json('ip_whitelist')->nullable(); // array of IP addresses
            $table->integer('session_timeout')->default(120); // minutes
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('security_settings');
    }
};
