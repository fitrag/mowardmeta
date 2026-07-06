<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('license_plans', function (Blueprint $table) {
            $table->integer('duration_days')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('license_plans', function (Blueprint $table) {
            $table->integer('duration_days')->nullable(false)->change();
        });
    }
};
