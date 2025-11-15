<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->onDelete('cascade');
            $table->string('invoice_number')->unique(); // INV-XXXXX
            $table->enum('status', ['unpaid', 'paid', 'cancelled', 'refunded', 'overdue'])->default('unpaid');

            // Amounts
            $table->decimal('subtotal', 15, 2);
            $table->decimal('tax_rate', 5, 2)->default(0); // percentage
            $table->decimal('tax_amount', 15, 2)->default(0);
            $table->decimal('total', 15, 2);
            $table->string('currency', 3)->default('USD');

            // Dates
            $table->date('due_date');
            $table->timestamp('paid_at')->nullable();

            // Payment info
            $table->string('payment_method')->nullable(); // gateway name

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
