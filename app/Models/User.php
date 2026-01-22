<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'is_active',
        'is_subscribed',
        'subscription_expires_at',
        'bonus_credits',
        'gemini_api_key',
        'google_id',
        'avatar',
        'theme',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'gemini_api_key',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'is_subscribed' => 'boolean',
            'subscription_expires_at' => 'datetime',
            'gemini_api_key' => 'encrypted',
        ];
    }

    /**
     * Check if user has a personal API key
     */
    public function hasPersonalApiKey(): bool
    {
        return $this->isSubscribed() && !empty($this->gemini_api_key);
    }

    /**
     * Check if user is admin
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /**
     * Check if user is regular user
     */
    public function isUser(): bool
    {
        return $this->role === 'user';
    }

    /**
     * Check if user has active subscription
     */
    public function isSubscribed(): bool
    {
        // Admin always has access
        if ($this->isAdmin()) {
            return true;
        }

        // Check if subscription is active and not expired
        if (!$this->is_subscribed) {
            return false;
        }

        // If no expiry date, subscription is active indefinitely
        if (!$this->subscription_expires_at) {
            return true;
        }

        return $this->subscription_expires_at->isFuture();
    }

    /**
     * Check if user can generate metadata
     */
    public function canGenerate(): bool
    {
        // Subscribed users can always generate
        if ($this->isSubscribed()) {
            return true;
        }

        // Free users are limited by daily count + bonus credits
        $totalCredits = $this->getDailyLimit() + $this->bonus_credits;
        return $this->getTodayGenerationCount() < $totalCredits;
    }

    /**
     * Get today's generation count
     */
    public function getTodayGenerationCount(): int
    {
        return cache()->remember(
            "user_{$this->id}_today_gen_count_" . today()->format('Y-m-d'),
            60, // 1 minute cache
            fn() => $this->metadataGenerations()
                ->whereDate('created_at', today())
                ->count()
        );
    }

    /**
     * Get remaining generations for free users
     */
    public function getRemainingGenerations(): int
    {
        if ($this->isSubscribed()) {
            return PHP_INT_MAX; // Unlimited
        }

        // Daily limit + bonus credits - today's usage
        $totalCredits = $this->getDailyLimit() + $this->bonus_credits;
        $remaining = $totalCredits - $this->getTodayGenerationCount();
        return max(0, $remaining);
    }

    /**
     * Get total available credits (daily + bonus)
     */
    public function getTotalCredits(): int
    {
        if ($this->isSubscribed()) {
            return PHP_INT_MAX;
        }
        return $this->getDailyLimit() + $this->bonus_credits;
    }

    /**
     * Consume one bonus credit after using daily limit
     */
    public function consumeBonusCredit(): void
    {
        if ($this->bonus_credits > 0 && $this->getTodayGenerationCount() >= $this->getDailyLimit()) {
            $this->decrement('bonus_credits');
        }
    }

    /**
     * Get daily generation limit for free users
     */
    public function getDailyLimit(): int
    {
        return cache()->remember(
            'app_free_user_daily_limit',
            3600, // 1 hour cache
            fn() => (int) Setting::getValue('free_user_daily_limit', 5)
        );
    }

    /**
     * Get subscription status label
     */
    public function getSubscriptionStatusAttribute(): string
    {
        if ($this->isSubscribed()) {
            if ($this->subscription_expires_at) {
                return 'Subscribed until ' . $this->subscription_expires_at->format('d M Y');
            }
            return 'Subscribed';
        }
        return 'Free';
    }

    /**
     * Get metadata generations for user
     */
    public function metadataGenerations(): HasMany
    {
        return $this->hasMany(MetadataGeneration::class);
    }

    /**
     * Get subscription orders for user
     */
    public function subscriptionOrders(): HasMany
    {
        return $this->hasMany(SubscriptionOrder::class);
    }

    /**
     * Check if user has a pending subscription order
     */
    public function hasPendingOrder(): bool
    {
        return $this->subscriptionOrders()->pending()->exists();
    }

    /**
     * Get the latest pending order
     */
    public function latestPendingOrder()
    {
        return $this->subscriptionOrders()->pending()->latest()->first();
    }
}
