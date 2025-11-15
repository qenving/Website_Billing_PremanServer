<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add is_owner column to users table
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('is_owner')->default(false)->after('is_active');
        });

        // Add slug and is_protected columns to roles table
        Schema::table('roles', function (Blueprint $table) {
            $table->string('slug')->unique()->nullable()->after('name');
            $table->boolean('is_protected')->default(false)->after('is_system');
        });

        // Update existing roles to have slug based on name
        DB::table('roles')->get()->each(function ($role) {
            DB::table('roles')
                ->where('id', $role->id)
                ->update(['slug' => \Illuminate\Support\Str::slug($role->name)]);
        });

        // Make slug non-nullable after populating existing records
        Schema::table('roles', function (Blueprint $table) {
            $table->string('slug')->unique()->nullable(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('is_owner');
        });

        Schema::table('roles', function (Blueprint $table) {
            $table->dropColumn(['slug', 'is_protected']);
        });
    }
};
