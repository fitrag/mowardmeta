<?php

namespace App\Livewire\Admin;

use App\Models\ApiKey;
use App\Services\AI\AIService;
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

    #[Validate('required|min:2|max:255')]
    public string $api_key = '';

    #[Validate('required')]
    public string $provider = 'gemini';

    #[Validate('nullable|url')]
    public ?string $base_url = null;

    #[Validate('nullable|string')]
    public ?string $models = null;

    #[Validate('boolean')]
    public bool $is_custom = false;

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
        $this->base_url = $apiKey->base_url;
        $this->models = is_array($apiKey->models) ? json_encode($apiKey->models, JSON_PRETTY_PRINT) : $apiKey->models;
        $this->is_custom = $apiKey->is_custom;
        $this->is_active = $apiKey->is_active;

        $this->showModal = true;
        $this->isEditing = true;
    }

    public function save(): void
    {
        $validated = $this->validate();

        $data = [
            'name' => $validated['name'],
            'api_key' => $validated['api_key'],
            'provider' => $validated['provider'],
            'is_active' => $validated['is_active'],
            'is_custom' => $this->is_custom,
        ];

        if ($this->is_custom) {
            $data['base_url'] = $validated['base_url'];
            $data['models'] = $this->parseModels($validated['models']);
        }

        if ($this->isEditing) {
            ApiKey::find($this->editingKeyId)->update($data);
        } else {
            ApiKey::create($data);
        }

        AIService::invalidateApiKeyCache($this->provider);

        $this->closeModal();
    }

    protected function parseModels(?string $modelsJson): ?array
    {
        if (empty($modelsJson)) {
            return null;
        }

        $models = json_decode($modelsJson, true);

        if (! is_array($models)) {
            $lines = array_filter(array_map('trim', explode("\n", $modelsJson)));
            $models = [];
            foreach ($lines as $line) {
                if (empty($line)) {
                    continue;
                }
                $models[] = ['name' => $line, 'label' => $line];
            }
        }

        return $models;
    }

    public function toggleActive(int $keyId): void
    {
        $apiKey = ApiKey::findOrFail($keyId);
        $apiKey->update(['is_active' => ! $apiKey->is_active]);
        AIService::invalidateApiKeyCache($apiKey->provider);
    }

    public function delete(int $keyId): void
    {
        $apiKey = ApiKey::findOrFail($keyId);
        AIService::invalidateApiKeyCache($apiKey->provider);
        $apiKey->delete();
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
        $this->base_url = null;
        $this->models = null;
        $this->is_custom = false;
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
