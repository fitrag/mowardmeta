<?php

namespace App\Livewire\User;

use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.app')]
#[Title('Dashboard')]
class Dashboard extends Component
{
    public function render()
    {
        $user = auth()->user();
        
        return view('livewire.user.dashboard', [
            'totalGenerations' => $user->metadataGenerations()->count(),
            'todayGenerations' => $user->metadataGenerations()->whereDate('created_at', today())->count(),
            'recentGenerations' => $user->metadataGenerations()->latest()->take(5)->get(),
        ]);
    }
}
