<?php

namespace App\Livewire\Admin;

use App\Models\AppSetting;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.app')]
#[Title('App Settings')]
class AppSettings extends Component
{
    public array $settings = [];
    public ?string $message = null;

    public function mount(): void
    {
        $this->loadSettings();
    }

    protected function loadSettings(): void
    {
        $allSettings = AppSetting::all();
        foreach ($allSettings as $setting) {
            $this->settings[$setting->key] = $setting->value;
        }
    }

    public function save(): void
    {
        foreach ($this->settings as $key => $value) {
            AppSetting::set($key, $value);
        }

        AppSetting::clearCache();
        $this->message = 'Settings saved successfully!';
    }

    public function render()
    {
        $groupedSettings = AppSetting::getAllGrouped();
        
        return view('livewire.admin.app-settings', [
            'groupedSettings' => $groupedSettings,
        ]);
    }
}
