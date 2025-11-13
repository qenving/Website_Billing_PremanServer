<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_extensions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('extension_id')->constrained()->onDelete('cascade');
            $table->json('supports_currencies')->nullable(); // ["USD", "IDR", "EUR"]
            $table->boolean('supports_refund')->default(false);
            $table->boolean('supports_recurring')->default(false);
            $table->enum('fee_type', ['fixed', 'percentage', 'none'])->default('none');
            $table->decimal('fee_amount', 10, 2)->default(0);
            $table->decimal('min_transaction', 10, 2)->nullable();
            $table->decimal('max_transaction', 10, 2)->nullable();
            $table->boolean('test_mode')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_extensions');
    }
};
