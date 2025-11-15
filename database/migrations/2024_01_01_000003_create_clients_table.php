<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('company_name')->nullable();
            $table->string('phone', 20)->nullable();
            $table->text('address')->nullable();
            $table->string('city', 100)->nullable();
            $table->string('state', 100)->nullable();
            $table->string('country', 2)->nullable(); // ISO 3166-1 alpha-2
            $table->string('postal_code', 20)->nullable();
            $table->string('tax_id', 50)->nullable(); // NPWP, VAT, etc
            $table->string('currency', 3)->default('USD'); // ISO 4217
            $table->string('language', 5)->default('en'); // en, id, etc
            $table->decimal('total_spent', 15, 2)->default(0);
            $table->enum('status', ['active', 'suspended', 'blocked'])->default('active');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clients');
    }
};
