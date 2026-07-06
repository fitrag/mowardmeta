<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LicensePlan extends Model
{
    protected $fillable = [
        'name',
        'license_type',
        'product_name',
        'duration_days',
        'credits_amount',
        'price',
        'max_activations',
        'description',
        'features',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'features' => 'array',
            'duration_days' => 'integer',
            'credits_amount' => 'integer',
            'price' => 'integer',
            'max_activations' => 'integer',
            'sort_order' => 'integer',
        ];
    }

    public function orders(): HasMany
    {
        return $this->hasMany(LicenseOrder::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('price');
    }

    public static function getActive()
    {
        return static::active()->ordered()->get();
    }

    public function isDurationBased(): bool
    {
        return $this->license_type === 'duration';
    }

    public function isCreditsBased(): bool
    {
        return $this->license_type === 'credits';
    }

    public function getFormattedPriceAttribute(): string
    {
        return 'Rp ' . number_format($this->price, 0, ',', '.');
    }

    public function getDurationLabelAttribute(): string
    {
        if ($this->isCreditsBased()) {
            return $this->credits_amount . ' Credits';
        }

        if ($this->duration_days >= 365) {
            $years = floor($this->duration_days / 365);
            return $years . ' Tahun';
        } elseif ($this->duration_days >= 30) {
            $months = floor($this->duration_days / 30);
            return $months . ' Bulan';
        } else {
            return $this->duration_days . ' Hari';
        }
    }

    public function getLicenseTypeLabelAttribute(): string
    {
        return match ($this->license_type) {
            'duration' => 'Duration',
            'credits' => 'Credits',
            default => 'Unknown',
        };
    }
}
