<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('menu_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parent_id')->nullable()->constrained('menu_items')->onDelete('cascade');
            $table->enum('position', ['client_area', 'admin_area', 'footer'])->default('client_area');

            $table->string('label');
            $table->string('url');
            $table->string('icon', 50)->nullable(); // icon class atau nama
            $table->enum('target', ['_self', '_blank'])->default('_self');
            $table->enum('visibility', ['always', 'authenticated', 'guest'])->default('always');

            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('menu_items');
    }
};
