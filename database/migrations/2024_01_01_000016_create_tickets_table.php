<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->onDelete('cascade');
            $table->string('ticket_number')->unique(); // TKT-XXXXX
            $table->foreignId('department_id')->nullable()->constrained('ticket_departments')->onDelete('set null');
            $table->foreignId('service_id')->nullable()->constrained()->onDelete('set null');

            $table->string('subject');
            $table->enum('status', ['open', 'answered', 'customer_reply', 'on_hold', 'closed'])->default('open');
            $table->enum('priority', ['low', 'medium', 'high', 'urgent'])->default('medium');

            $table->timestamp('last_reply_at')->nullable();
            $table->timestamp('closed_at')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};
