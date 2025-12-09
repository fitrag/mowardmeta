<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MetadataGeneration extends Model
{
    protected $fillable = [
        'user_id',
        'filename',
        'image_path',
        'title',
        'keywords',
        'ai_response',
        'ai_model',
    ];

    protected function casts(): array
    {
        return [
            'ai_response' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get keywords as array
     */
    public function getKeywordsArray(): array
    {
        if (empty($this->keywords)) {
            return [];
        }
        return array_map('trim', explode(',', $this->keywords));
    }
}
