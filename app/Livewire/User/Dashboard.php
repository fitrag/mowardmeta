<?php

namespace App\Livewire\User;

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
        ]);
    }
}
