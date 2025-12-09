<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApiKey extends Model
{
    protected $fillable = [
        'name',
        'provider',
        'api_key',
        'is_active',
        'usage_count',
        'last_used_at',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'last_used_at' => 'datetime',
        ];
    }

    /**
     * Get a random active API key
     */
    public static function getRandomActive(string $provider = 'gemini'): ?self
    {
        return static::where('provider', $provider)
            ->where('is_active', true)
            ->inRandomOrder()
            ->first();
    }

    /**
     * Increment usage count
     */
    public function incrementUsage(): void
    {
        $this->increment('usage_count');
        $this->update(['last_used_at' => now()]);
    }
}
