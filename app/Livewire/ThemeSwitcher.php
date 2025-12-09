<?php

namespace App\Livewire;

use Livewire\Component;

class ThemeSwitcher extends Component
{
    public string $theme = 'dark';

    public function mount(): void
    {
        // Theme is managed entirely by localStorage via JavaScript
        // This component just provides the UI for switching
        $this->theme = 'dark'; // Default, will be overridden by JS
    }

    public function setTheme(string $theme): void
    {
        if (!in_array($theme, ['dark', 'light', 'system'])) {
            return;
        }

        $this->theme = $theme;
        
        // Theme is saved to localStorage via Alpine.js
        // No need to save to database
        $this->dispatch('theme-changed', theme: $theme);
    }

    public function render()
    {
        return view('livewire.theme-switcher');
    }
}
