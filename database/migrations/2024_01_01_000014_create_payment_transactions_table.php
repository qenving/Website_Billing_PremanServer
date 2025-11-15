<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payment_extension_id')->constrained()->onDelete('cascade');
            $table->foreignId('payment_id')->nullable()->constrained()->onDelete('set null');

            $table->string('transaction_reference')->nullable();
            $table->enum('type', ['callback', 'webhook', 'status_check', 'refund'])->default('callback');
            $table->string('status', 50)->nullable();
            $table->text('raw_payload')->nullable(); // JSON payload mentah
            $table->string('ip_address', 45)->nullable();

            $table->timestamp('processed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_transactions');
    }
};
