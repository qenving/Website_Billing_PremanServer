<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('provisioning_extensions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('extension_id')->constrained()->onDelete('cascade');
            $table->string('api_endpoint')->nullable();
            $table->string('api_version', 20)->nullable();
            $table->timestamp('last_health_check_at')->nullable();
            $table->enum('health_status', ['ok', 'warning', 'error'])->default('ok');
            $table->text('health_message')->nullable();
            $table->json('capabilities')->nullable(); // ["reboot", "console", "rebuild", "backup"]
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('provisioning_extensions');
    }
};
