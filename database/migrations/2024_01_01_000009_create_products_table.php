<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_group_id')->nullable()->constrained()->onDelete('set null');
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->enum('type', ['vps', 'dedicated', 'game_server', 'web_hosting', 'other'])->default('other');
            $table->enum('pricing_model', ['recurring', 'one_time'])->default('recurring');
            $table->decimal('price', 15, 2);
            $table->enum('billing_cycle', ['monthly', 'quarterly', 'semi_annually', 'annually', 'biennially', 'triennially'])->nullable();
            $table->string('currency', 3)->default('USD');
            $table->decimal('setup_fee', 15, 2)->default(0);

            // Stock control
            $table->boolean('stock_control')->default(false);
            $table->integer('stock_quantity')->nullable();

            // Provisioning
            $table->foreignId('provisioning_extension_id')->nullable()->constrained()->onDelete('set null');
            $table->json('provisioning_config')->nullable(); // plan_id, template, resources, etc

            // Payment restrictions
            $table->json('allowed_payment_extensions')->nullable(); // array of extension IDs, null = all allowed

            $table->boolean('is_visible')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
