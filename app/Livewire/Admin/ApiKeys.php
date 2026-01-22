<?php

namespace App\Livewire\Admin;

use App\Models\ApiKey;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
#[Title('API Keys')]
class ApiKeys extends Component
{
    use WithPagination;

    public bool $showModal = false;
    public bool $isEditing = false;
    public ?int $editingKeyId = null;

    #[Validate('required|min:2|max:255')]
    public string $name = '';

    #[Validate('required')]
    public string $api_key = '';

    #[Validate('required|in:gemini,groq,mistral')]
    public string $provider = 'gemini';

    #[Validate('boolean')]
    public bool $is_active = true;

    public function openCreateModal(): void
    {
        $this->resetForm();
        $this->showModal = true;
        $this->isEditing = false;
    }

    public function openEditModal(int $keyId): void
    {
        $apiKey = ApiKey::findOrFail($keyId);
        
        $this->editingKeyId = $apiKey->id;
        $this->name = $apiKey->name;
        $this->api_key = $apiKey->api_key;
        $this->provider = $apiKey->provider;
        $this->is_active = $apiKey->is_active;
        
        $this->showModal = true;
        $this->isEditing = true;
    }

    public function save(): void
    {
        $this->validate();

        $data = [
            'name' => $this->name,
            'api_key' => $this->api_key,
            'provider' => $this->provider,
            'is_active' => $this->is_active,
        ];

        if ($this->isEditing) {
            ApiKey::find($this->editingKeyId)->update($data);
        } else {
            ApiKey::create($data);
        }

        $this->closeModal();
    }

    public function toggleActive(int $keyId): void
    {
        $apiKey = ApiKey::findOrFail($keyId);
        $apiKey->update(['is_active' => !$apiKey->is_active]);
    }

    public function delete(int $keyId): void
    {
        ApiKey::findOrFail($keyId)->delete();
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->resetForm();
    }

    protected function resetForm(): void
    {
        $this->name = '';
        $this->api_key = '';
        $this->provider = 'gemini';
        $this->is_active = true;
        $this->editingKeyId = null;
        $this->resetValidation();
    }

    public function render()
    {
        return view('livewire.admin.api-keys', [
            'apiKeys' => ApiKey::latest()->paginate(10),
        ]);
    }
}
