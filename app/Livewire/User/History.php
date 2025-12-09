<?php

namespace App\Livewire\User;

use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.app')]
#[Title('History')]
class History extends Component
{
    public function render()
    {
        return view('livewire.user.history');
    }
}
