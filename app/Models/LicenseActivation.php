<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LicenseActivation extends Model
{
    protected $fillable = [
        'license_id',
        'device_identifier',
        'ip_address',
        'user_agent',
        'last_check_at',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'last_check_at' => 'datetime',
            'is_active' => 'boolean',
        ];
    }

    public function license(): BelongsTo
    {
        return $this->belongsTo(License::class);
    }

    public function updateLastCheck(): void
    {
        $this->update(['last_check_at' => now()]);
    }
}
