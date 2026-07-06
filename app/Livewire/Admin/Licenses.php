<?php

namespace App\Livewire\Admin;

use App\Models\License;
use App\Models\User;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
#[Title('Licenses')]
class Licenses extends Component
{
    use WithPagination;

    public string $search = '';
    public string $statusFilter = '';
    public string $typeFilter = '';
    public bool $showModal = false;
    public bool $isEditing = false;
    public ?int $editingId = null;

    // Form fields
    public string $user_id = '';
    public string $product_name = '';
    public string $domain = '';
    public string $status = 'active';
    public string $license_type = 'duration';
    public ?string $expires_at = null;
    public ?int $credits_total = null;
    public int $credits_used = 0;
    public int $max_activations = 1;
    public string $notes = '';

    protected function rules(): array
    {
        $rules = [
            'user_id' => 'required|exists:users,id',
            'product_name' => 'required|string|max:255',
            'domain' => 'nullable|string|max:255',
            'status' => 'required|in:active,pending,expired,revoked',
            'license_type' => 'required|in:duration,credits',
            'max_activations' => 'required|integer|min:1',
            'notes' => 'nullable|string|max:1000',
        ];

        if ($this->license_type === 'duration') {
            $rules['expires_at'] = 'nullable|date';
        } else {
            $rules['credits_total'] = 'required|integer|min:1';
            $rules['credits_used'] = 'integer|min:0';
        }

        return $rules;
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingStatusFilter(): void
    {
        $this->resetPage();
    }

    public function updatingTypeFilter(): void
    {
        $this->resetPage();
    }

    public function openCreateModal(): void
    {
        $this->resetForm();
        $this->showModal = true;
        $this->isEditing = false;
    }

    public function openEditModal(int $id): void
    {
        $license = License::findOrFail($id);

        $this->editingId = $license->id;
        $this->user_id = (string) $license->user_id;
        $this->product_name = $license->product_name;
        $this->domain = $license->domain ?? '';
        $this->status = $license->status;
        $this->license_type = $license->license_type ?? 'duration';
        $this->expires_at = $license->expires_at?->format('Y-m-d');
        $this->credits_total = $license->credits_total;
        $this->credits_used = $license->credits_used ?? 0;
        $this->max_activations = $license->max_activations;
        $this->notes = $license->notes ?? '';

        $this->showModal = true;
        $this->isEditing = true;
    }

    public function save(): void
    {
        $this->validate();

        $data = [
            'user_id' => $this->user_id,
            'product_name' => $this->product_name,
            'domain' => $this->domain ?: null,
            'status' => $this->status,
            'license_type' => $this->license_type,
            'max_activations' => $this->max_activations,
            'notes' => $this->notes ?: null,
        ];

        if ($this->license_type === 'duration') {
            $data['expires_at'] = $this->expires_at ?: null;
            $data['credits_total'] = null;
            $data['credits_used'] = 0;
        } else {
            $data['expires_at'] = null;
            $data['credits_total'] = $this->credits_total;
            $data['credits_used'] = $this->credits_used;
        }

        if ($this->status === 'active' && !$this->isEditing) {
            $data['activated_at'] = now();
        }

        if ($this->isEditing) {
            License::find($this->editingId)->update($data);
        } else {
            License::create($data);
        }

        $this->closeModal();
    }

    public function regenerateKey(int $id): void
    {
        $license = License::findOrFail($id);
        $license->update(['license_key' => License::generateLicenseKey()]);
    }

    public function revoke(int $id): void
    {
        $license = License::findOrFail($id);
        $license->revoke();
    }

    public function delete(int $id): void
    {
        License::findOrFail($id)->delete();
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->resetForm();
    }

    protected function resetForm(): void
    {
        $this->user_id = '';
        $this->product_name = '';
        $this->domain = '';
        $this->status = 'active';
        $this->license_type = 'duration';
        $this->expires_at = null;
        $this->credits_total = null;
        $this->credits_used = 0;
        $this->max_activations = 1;
        $this->notes = '';
        $this->editingId = null;
        $this->resetValidation();
    }

    public function render()
    {
        $query = License::with('user')
            ->when($this->search, function ($q) {
                $q->where(function ($q) {
                    $q->where('license_key', 'like', "%{$this->search}%")
                        ->orWhere('product_name', 'like', "%{$this->search}%")
                        ->orWhere('domain', 'like', "%{$this->search}%")
                        ->orWhereHas('user', fn($q) => $q->where('name', 'like', "%{$this->search}%")
                            ->orWhere('email', 'like', "%{$this->search}%"));
                });
            })
            ->when($this->statusFilter, fn($q) => $q->where('status', $this->statusFilter))
            ->when($this->typeFilter, fn($q) => $q->where('license_type', $this->typeFilter))
            ->latest();

        return view('livewire.admin.licenses', [
            'licenses' => $query->paginate(15),
            'users' => User::orderBy('name')->get(['id', 'name', 'email']),
            'stats' => [
                'total' => License::count(),
                'active' => License::where('status', 'active')->count(),
                'duration' => License::where('license_type', 'duration')->count(),
                'credits' => License::where('license_type', 'credits')->count(),
            ],
        ]);
    }
}
