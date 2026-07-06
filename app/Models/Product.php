<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Product extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'short_description',
        'description',
        'thumbnail',
        'type',
        'price',
        'sale_price',
        'file_path',
        'file_name',
        'file_size',
        'version',
        'demo_url',
        'documentation_url',
        'features',
        'requirements',
        'requires_license',
        'license_duration_days',
        'download_count',
        'is_active',
        'is_featured',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'features' => 'array',
            'requirements' => 'array',
            'requires_license' => 'boolean',
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
        ];
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($product) {
            if (empty($product->slug)) {
                $product->slug = Str::slug($product->name);
            }
        });
    }

    public function orders(): HasMany
    {
        return $this->hasMany(ProductOrder::class);
    }

    public function downloads(): HasMany
    {
        return $this->hasMany(ProductDownload::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function scopeFree($query)
    {
        return $query->where('type', 'free');
    }

    public function scopePaid($query)
    {
        return $query->where('type', 'paid');
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('created_at', 'desc');
    }

    public function isFree(): bool
    {
        return $this->type === 'free';
    }

    public function isPaid(): bool
    {
        return $this->type === 'paid';
    }

    public function hasDiscount(): bool
    {
        return $this->sale_price !== null && $this->sale_price < $this->price;
    }

    public function getCurrentPrice(): int
    {
        return $this->sale_price ?? $this->price;
    }

    public function getDiscountPercentage(): int
    {
        if (!$this->hasDiscount()) {
            return 0;
        }
        return round((($this->price - $this->sale_price) / $this->price) * 100);
    }

    public function getFormattedPriceAttribute(): string
    {
        if ($this->isFree()) {
            return 'Gratis';
        }
        return 'Rp ' . number_format($this->getCurrentPrice(), 0, ',', '.');
    }

    public function getFormattedOriginalPriceAttribute(): string
    {
        return 'Rp ' . number_format($this->price, 0, ',', '.');
    }

    public function getFormattedFileSizeAttribute(): string
    {
        if (!$this->file_size) {
            return '-';
        }

        $bytes = $this->file_size;
        $units = ['B', 'KB', 'MB', 'GB'];
        $i = 0;

        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }

        return round($bytes, 2) . ' ' . $units[$i];
    }

    public function userHasPurchased(?User $user): bool
    {
        if (!$user) {
            return false;
        }

        if ($this->isFree()) {
            return true;
        }

        return $this->orders()
            ->where('user_id', $user->id)
            ->where('status', 'approved')
            ->exists();
    }

    public function userHasPendingOrder(?User $user): bool
    {
        if (!$user) {
            return false;
        }

        return $this->orders()
            ->where('user_id', $user->id)
            ->where('status', 'pending')
            ->exists();
    }
}
