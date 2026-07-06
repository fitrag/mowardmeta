<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add to licenses table
        Schema::table('licenses', function (Blueprint $table) {
            $table->enum('license_type', ['duration', 'credits'])->default('duration')->after('status');
            $table->integer('credits_total')->nullable()->after('license_type');
            $table->integer('credits_used')->default(0)->after('credits_total');
        });

        // Add to license_plans table
        Schema::table('license_plans', function (Blueprint $table) {
            $table->enum('license_type', ['duration', 'credits'])->default('duration')->after('name');
            $table->integer('credits_amount')->nullable()->after('duration_days');
        });

        // Add to products table
        Schema::table('products', function (Blueprint $table) {
            $table->enum('license_type', ['duration', 'credits'])->default('duration')->after('requires_license');
            $table->integer('license_credits')->nullable()->after('license_duration_days');
        });
    }

    public function down(): void
    {
        Schema::table('licenses', function (Blueprint $table) {
            $table->dropColumn(['license_type', 'credits_total', 'credits_used']);
        });

        Schema::table('license_plans', function (Blueprint $table) {
            $table->dropColumn(['license_type', 'credits_amount']);
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['license_type', 'license_credits']);
        });
    }
};
