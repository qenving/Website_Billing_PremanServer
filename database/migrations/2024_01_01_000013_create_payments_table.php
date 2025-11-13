<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained()->onDelete('cascade');
            $table->foreignId('client_id')->constrained()->onDelete('cascade');
            $table->foreignId('payment_extension_id')->nullable()->constrained()->onDelete('set null');

            $table->string('transaction_id')->unique(); // dari gateway
            $table->string('payment_number')->unique(); // PAY-XXXXX (internal)

            $table->decimal('amount', 15, 2);
            $table->string('currency', 3)->default('USD');
            $table->enum('status', ['pending', 'completed', 'failed', 'refunded', 'expired'])->default('pending');

            $table->string('payment_method')->nullable(); // gateway name
            $table->decimal('gateway_fee', 15, 2)->default(0);
            $table->json('gateway_response')->nullable(); // raw response dari gateway

            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
