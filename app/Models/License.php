<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class License extends Model
{
    protected $fillable = [
        'user_id',
        'license_key',
        'product_name',
        'domain',
        'status',
        'license_type',
        'credits_total',
        'credits_used',
        'activated_at',
        'expires_at',
        'max_activations',
        'current_activations',
        'metadata',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'activated_at' => 'datetime',
            'expires_at' => 'datetime',
            'metadata' => 'array',
            'credits_total' => 'integer',
            'credits_used' => 'integer',
            'max_activations' => 'integer',
            'current_activations' => 'integer',
        ];
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($license) {
            if (empty($license->license_key)) {
                $license->license_key = static::generateLicenseKey();
            }
        });
    }

    public static function generateLicenseKey(): string
    {
        do {
            $key = strtoupper(implode('-', [
                Str::random(4),
                Str::random(4),
                Str::random(4),
                Str::random(4),
            ]));
        } while (static::where('license_key', $key)->exists());

        return $key;
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function activations(): HasMany
    {
        return $this->hasMany(LicenseActivation::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(LicenseOrder::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeExpired($query)
    {
        return $query->where('status', 'expired');
    }

    public function scopeValid($query)
    {
        return $query->where('status', 'active')
            ->where(function ($q) {
                $q->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            });
    }

    public function isDurationBased(): bool
    {
        // Default to duration if license_type is null (for backward compatibility)
        return $this->license_type === 'duration' || $this->license_type === null;
    }

    public function isCreditsBased(): bool
    {
        return $this->license_type === 'credits';
    }

    public function isValid(): bool
    {
        if ($this->status !== 'active') {
            return false;
        }

        // Check duration-based expiration
        if ($this->isDurationBased() && $this->expires_at && $this->expires_at->isPast()) {
            return false;
        }

        // Check credits-based expiration
        if ($this->isCreditsBased() && $this->credits_total !== null && $this->credits_used >= $this->credits_total) {
            return false;
        }

        return true;
    }

    public function isExpired(): bool
    {
        if ($this->isDurationBased()) {
            return $this->expires_at && $this->expires_at->isPast();
        }

        if ($this->isCreditsBased()) {
            return $this->credits_total !== null && $this->credits_used >= $this->credits_total;
        }

        return false;
    }

    public function canActivate(): bool
    {
        return $this->current_activations < $this->max_activations;
    }

    public function hasCredits(): bool
    {
        if (!$this->isCreditsBased()) {
            return true; // Duration-based always has "credits"
        }

        return $this->credits_total === null || $this->credits_used < $this->credits_total;
    }

    public function getCreditsRemaining(): ?int
    {
        if (!$this->isCreditsBased() || $this->credits_total === null) {
            return null;
        }

        return max(0, $this->credits_total - $this->credits_used);
    }

    public function useCredit(int $amount = 1): bool
    {
        if (!$this->isCreditsBased()) {
            return true;
        }

        if (!$this->hasCredits()) {
            return false;
        }

        $this->increment('credits_used', $amount);
        return true;
    }

    public function activate(?string $domain = null): bool
    {
        if (!$this->canActivate()) {
            return false;
        }

        $this->update([
            'status' => 'active',
            'activated_at' => $this->activated_at ?? now(),
            'domain' => $domain ?? $this->domain,
            'current_activations' => $this->current_activations + 1,
        ]);

        return true;
    }

    public function revoke(): void
    {
        $this->update(['status' => 'revoked']);
        $this->activations()->update(['is_active' => false]);
    }

    public function getDaysRemainingAttribute(): ?int
    {
        if (!$this->isDurationBased() || !$this->expires_at) {
            return null;
        }

        return max(0, now()->diffInDays($this->expires_at, false));
    }

    public function getStatusColorAttribute(): string
    {
        if ($this->isExpired()) {
            return 'red';
        }

        return match ($this->status) {
            'active' => 'emerald',
            'expired' => 'red',
            'revoked' => 'red',
            'pending' => 'amber',
            default => 'gray',
        };
    }

    public function getStatusLabelAttribute(): string
    {
        if ($this->status === 'active' && $this->isExpired()) {
            return $this->isCreditsBased() ? 'Credits Exhausted' : 'Expired';
        }

        return match ($this->status) {
            'active' => 'Active',
            'expired' => 'Expired',
            'revoked' => 'Revoked',
            'pending' => 'Pending',
            default => 'Unknown',
        };
    }

    public function getLicenseTypeLabelAttribute(): string
    {
        return match ($this->license_type) {
            'duration' => 'Duration Based',
            'credits' => 'Credits Based',
            default => 'Unknown',
        };
    }
}
