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
        'base_url',
        'models',
        'is_custom',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'is_custom' => 'boolean',
            'models' => 'array',
            'last_used_at' => 'datetime',
            'usage_count' => 'integer',
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
     * Increment usage count in a single query
     */
    public function incrementUsage(): void
    {
        $this->newQuery()
            ->where('id', $this->id)
            ->update([
                'usage_count' => $this->usage_count + 1,
                'last_used_at' => now(),
            ]);

        $this->usage_count++;
        $this->last_used_at = now();
    }
}
