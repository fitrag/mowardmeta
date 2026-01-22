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
        // Users table indexes
        Schema::table('users', function (Blueprint $table) {
            $table->index('role');
            $table->index('is_active');
            $table->index('is_subscribed');
        });

        // Metadata generations table indexes
        Schema::table('metadata_generations', function (Blueprint $table) {
            $table->index('user_id');
            $table->index('created_at');
        });

        // API keys table indexes
        Schema::table('api_keys', function (Blueprint $table) {
            $table->index('provider');
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['role']);
            $table->dropIndex(['is_active']);
            $table->dropIndex(['is_subscribed']);
        });

        Schema::table('metadata_generations', function (Blueprint $table) {
            $table->dropIndex(['user_id']);
            $table->dropIndex(['created_at']);
        });

        Schema::table('api_keys', function (Blueprint $table) {
            $table->dropIndex(['provider']);
            $table->dropIndex(['is_active']);
        });
    }
};
