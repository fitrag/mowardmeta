<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('app_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->string('type')->default('text'); // text, textarea, number, boolean
            $table->string('group')->default('general'); // general, seo, limits, etc.
            $table->string('label');
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // Insert default settings
        $settings = [
            // General
            ['key' => 'app_name', 'value' => 'MetaGen', 'type' => 'text', 'group' => 'general', 'label' => 'Application Name', 'description' => 'The name of your application'],
            ['key' => 'app_tagline', 'value' => 'AI-Powered Metadata Generator', 'type' => 'text', 'group' => 'general', 'label' => 'Tagline', 'description' => 'A short tagline for your app'],
            
            // SEO
            ['key' => 'meta_title', 'value' => 'MetaGen - AI Metadata Generator for Stock Photos', 'type' => 'text', 'group' => 'seo', 'label' => 'Meta Title', 'description' => 'SEO title for search engines'],
            ['key' => 'meta_description', 'value' => 'Generate SEO-optimized titles and keywords for your stock photos using AI. Perfect for Adobe Stock, Shutterstock, and Getty Images.', 'type' => 'textarea', 'group' => 'seo', 'label' => 'Meta Description', 'description' => 'SEO description for search engines'],
            ['key' => 'meta_keywords', 'value' => 'metadata generator, stock photo keywords, AI metadata, SEO keywords, adobe stock, shutterstock', 'type' => 'textarea', 'group' => 'seo', 'label' => 'Meta Keywords', 'description' => 'SEO keywords separated by comma'],
            
            // Limits
            ['key' => 'free_user_daily_limit', 'value' => '5', 'type' => 'number', 'group' => 'limits', 'label' => 'Free User Daily Limit', 'description' => 'Maximum generations per day for free users'],
            ['key' => 'default_keyword_count', 'value' => '35', 'type' => 'number', 'group' => 'limits', 'label' => 'Default Keyword Count', 'description' => 'Default number of keywords to generate'],
        ];

        foreach ($settings as $setting) {
            \DB::table('app_settings')->insert(array_merge($setting, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('app_settings');
    }
};
