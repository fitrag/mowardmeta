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
            // Composite index for common query: user + today's count
            $table->index(['user_id', 'created_at']);
        });

        // API keys table indexes
        Schema::table('api_keys', function (Blueprint $table) {
            $table->index('provider');
            $table->index('is_active');
            // Composite index for common query: active keys by provider
            $table->index(['provider', 'is_active']);
        });

        // Subscription orders table indexes
        Schema::table('subscription_orders', function (Blueprint $table) {
            $table->index('status');
            // Composite index for pending orders by user
            $table->index(['user_id', 'status']);
        });

        // Settings table indexes (if not already unique)
        Schema::table('settings', function (Blueprint $table) {
            if (! Schema::hasIndex('settings', 'settings_key_unique')) {
                $table->unique('key');
            }
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
            $table->dropIndex(['user_id', 'created_at']);
        });

        Schema::table('api_keys', function (Blueprint $table) {
            $table->dropIndex(['provider']);
            $table->dropIndex(['is_active']);
            $table->dropIndex(['provider', 'is_active']);
        });

        Schema::table('subscription_orders', function (Blueprint $table) {
            $table->dropIndex(['status']);
            $table->dropIndex(['user_id', 'status']);
        });

        Schema::table('settings', function (Blueprint $table) {
            $table->dropUnique(['key']);
        });
    }
};
