<?php

namespace App\Livewire\User;

use App\Models\License;
use App\Models\Product;
use App\Models\ProductOrder;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.app')]
#[Title('Dashboard')]
class Dashboard extends Component
{
    #[Computed]
    public function user()
    {
        return auth()->user();
    }

    #[Computed]
    public function totalGenerations(): int
    {
        return $this->user->metadataGenerations()->count();
    }

    #[Computed]
    public function todayGenerations(): int
    {
        return $this->user->metadataGenerations()->whereDate('created_at', today())->count();
    }

    #[Computed]
    public function recentGenerations()
    {
        return $this->user->metadataGenerations()->latest()->take(5)->get();
    }

    #[Computed]
    public function isSubscribed(): bool
    {
        return $this->user->isSubscribed();
    }

    #[Computed]
    public function remainingGenerations(): int
    {
        return $this->user->getRemainingGenerations();
    }

    #[Computed]
    public function dailyLimit(): int
    {
        return $this->user->getDailyLimit();
    }

    #[Computed]
    public function subscriptionExpiresAt()
    {
        return $this->user->subscription_expires_at;
    }

    #[Computed]
    public function activeLicenses()
    {
        return License::where('user_id', $this->user->id)
            ->where('status', 'active')
            ->where(function ($q) {
                $q->where(function ($q2) {
                    // Duration-based: not expired
                    $q2->where(function ($q3) {
                        $q3->whereNull('license_type')
                            ->orWhere('license_type', 'duration');
                    })->where(function ($q3) {
                        $q3->whereNull('expires_at')
                            ->orWhere('expires_at', '>', now());
                    });
                })->orWhere(function ($q2) {
                    // Credits-based: has remaining credits
                    $q2->where('license_type', 'credits')
                        ->whereRaw('credits_used < credits_total');
                });
            })
            ->latest()
            ->get();
    }

    #[Computed]
    public function myProducts()
    {
        return ProductOrder::where('user_id', $this->user->id)
            ->where('status', 'approved')
            ->with('product')
            ->latest()
            ->take(5)
            ->get();
    }

    #[Computed]
    public function myProductsCount(): int
    {
        return ProductOrder::where('user_id', $this->user->id)
            ->where('status', 'approved')
            ->count();
    }

    #[Computed]
    public function newProducts()
    {
        return Product::active()
            ->ordered()
            ->latest()
            ->take(5)
            ->get();
    }

    public function render()
    {
        $user = auth()->user();
        
        return view('livewire.user.dashboard', [
            'user' => $user,
            'totalGenerations' => $user->metadataGenerations()->count(),
            'todayGenerations' => $user->getTodayGenerationCount(),
            'recentGenerations' => $user->metadataGenerations()->latest()->take(5)->get(),
            'isSubscribed' => $user->isSubscribed(),
            'remainingGenerations' => $user->getRemainingGenerations(),
            'dailyLimit' => $user->getDailyLimit(),
            'subscriptionExpiresAt' => $user->subscription_expires_at,
            'activeLicenses' => $this->activeLicenses,
            'myProducts' => $this->myProducts,
            'myProductsCount' => $this->myProductsCount,
            'newProducts' => $this->newProducts,
        ]);
    }
}
