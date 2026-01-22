<?php

namespace App\Livewire\Admin;

use App\Models\ApiKey;
use App\Models\MetadataGeneration;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.app')]
#[Title('Admin Dashboard')]
class Dashboard extends Component
{
    #[Computed(cache: true, seconds: 60)]
    public function totalUsers(): int
    {
        return Cache::remember('admin_total_users', 60, fn() => User::count());
    }

    #[Computed(cache: true, seconds: 60)]
    public function activeUsers(): int
    {
        return Cache::remember('admin_active_users', 60, fn() => User::where('is_active', true)->count());
    }

    #[Computed(cache: true, seconds: 30)]
    public function totalGenerations(): int
    {
        return Cache::remember('admin_total_generations', 30, fn() => MetadataGeneration::count());
    }

    #[Computed]
    public function todayGenerations(): int
    {
        return MetadataGeneration::whereDate('created_at', today())->count();
    }

    #[Computed(cache: true, seconds: 60)]
    public function activeApiKeys(): int
    {
        return Cache::remember('admin_active_api_keys', 60, fn() => ApiKey::where('is_active', true)->count());
    }

    #[Computed]
    public function recentUsers()
    {
        return User::latest()->take(5)->get();
    }

    #[Computed]
    public function recentGenerations()
    {
        return MetadataGeneration::with('user:id,name')->latest()->take(5)->get();
    }

    public function render()
    {
        return view('livewire.admin.dashboard', [
            'totalUsers' => $this->totalUsers,
            'activeUsers' => $this->activeUsers,
            'totalGenerations' => $this->totalGenerations,
            'activeApiKeys' => $this->activeApiKeys,
            'recentUsers' => $this->recentUsers,
            'recentGenerations' => $this->recentGenerations,
        ]);
    }
}
