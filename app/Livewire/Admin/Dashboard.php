<?php

namespace App\Livewire\Admin;

use App\Models\ApiKey;
use App\Models\MetadataGeneration;
use App\Models\User;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.app')]
#[Title('Admin Dashboard')]
class Dashboard extends Component
{
    public function render()
    {
        return view('livewire.admin.dashboard', [
            'totalUsers' => User::count(),
            'activeUsers' => User::where('is_active', true)->count(),
            'totalGenerations' => MetadataGeneration::count(),
            'todayGenerations' => MetadataGeneration::whereDate('created_at', today())->count(),
            'activeApiKeys' => ApiKey::where('is_active', true)->count(),
            'recentUsers' => User::latest()->take(5)->get(),
            'recentGenerations' => MetadataGeneration::with('user')->latest()->take(5)->get(),
        ]);
    }
}
