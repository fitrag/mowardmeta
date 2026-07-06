<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('api_keys', function (Blueprint $table) {
            $table->string('base_url')->nullable()->after('provider');
            $table->json('models')->nullable()->after('base_url');
            $table->boolean('is_custom')->default(false)->after('models');
        });
    }

    public function down(): void
    {
        Schema::table('api_keys', function (Blueprint $table) {
            $table->dropColumn(['base_url', 'models', 'is_custom']);
        });
    }
};
