<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Add AI provider setting
        DB::table('app_settings')->insert([
            'key' => 'ai_provider',
            'value' => 'gemini',
            'type' => 'select',
            'group' => 'ai',
            'label' => 'Default AI Provider',
            'description' => 'Select the default AI provider for metadata generation',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        DB::table('app_settings')->where('key', 'ai_provider')->delete();
    }
};
