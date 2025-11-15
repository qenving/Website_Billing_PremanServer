<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_id')->constrained()->onDelete('restrict');
            $table->foreignId('provisioning_extension_id')->nullable()->constrained()->onDelete('set null');
            $table->string('service_number')->unique(); // SRV-XXXXX

            // Status
            $table->enum('status', ['pending', 'active', 'suspended', 'terminated', 'cancelled'])->default('pending');
            $table->enum('provisioning_status', ['pending', 'processing', 'completed', 'failed'])->default('pending');

            // Provisioning data
            $table->string('provisioning_external_id')->nullable(); // ID dari panel eksternal
            $table->json('provisioning_data')->nullable(); // credentials, IP, access URLs, etc

            // Pricing
            $table->decimal('price', 15, 2);
            $table->string('currency', 3)->default('USD');
            $table->enum('billing_cycle', ['monthly', 'quarterly', 'semi_annually', 'annually', 'biennially', 'triennially'])->nullable();
            $table->date('next_due_date')->nullable();

            // Lifecycle
            $table->timestamp('registered_at')->nullable();
            $table->timestamp('terminated_at')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('services');
    }
};
