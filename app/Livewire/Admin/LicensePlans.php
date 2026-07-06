<?php

namespace App\Livewire\Admin;

use App\Models\LicensePlan;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.app')]
#[Title('License Plans')]
class LicensePlans extends Component
{
    public bool $showModal = false;
    public bool $isEditing = false;
    public ?int $editingId = null;

    public string $name = '';
    public string $product_name = '';
    public string $license_type = 'duration';
    public int $duration_days = 30;
    public ?int $credits_amount = null;
    public int $price = 0;
    public int $max_activations = 1;
    public string $description = '';
    public string $features = '';
    public bool $is_active = true;
    public int $sort_order = 0;

    protected function rules(): array
    {
        $rules = [
            'name' => 'required|min:2|max:255',
            'product_name' => 'required|min:2|max:255',
            'license_type' => 'required|in:duration,credits',
            'price' => 'required|integer|min:0',
            'max_activations' => 'required|integer|min:1',
            'description' => 'nullable|max:500',
            'features' => 'nullable|string',
            'is_active' => 'boolean',
            'sort_order' => 'integer|min:0',
        ];

        if ($this->license_type === 'duration') {
            $rules['duration_days'] = 'required|integer|min:1';
        } else {
            $rules['credits_amount'] = 'required|integer|min:1';
        }

        return $rules;
    }

    public function openCreateModal(): void
    {
        $this->resetForm();
        $this->showModal = true;
        $this->isEditing = false;
    }

    public function openEditModal(int $id): void
    {
        $plan = LicensePlan::findOrFail($id);

        $this->editingId = $plan->id;
        $this->name = $plan->name;
        $this->product_name = $plan->product_name;
        $this->license_type = $plan->license_type ?? 'duration';
        $this->duration_days = $plan->duration_days ?? 30;
        $this->credits_amount = $plan->credits_amount;
        $this->price = $plan->price;
        $this->max_activations = $plan->max_activations;
        $this->description = $plan->description ?? '';
        $this->features = $plan->features ? implode("\n", $plan->features) : '';
        $this->is_active = $plan->is_active;
        $this->sort_order = $plan->sort_order;

        $this->showModal = true;
        $this->isEditing = true;
    }

    public function save(): void
    {
        $this->validate();

        $features = array_filter(array_map('trim', explode("\n", $this->features)));

        $data = [
            'name' => $this->name,
            'product_name' => $this->product_name,
            'license_type' => $this->license_type,
            'price' => $this->price,
            'max_activations' => $this->max_activations,
            'description' => $this->description ?: null,
            'features' => !empty($features) ? $features : null,
            'is_active' => $this->is_active,
            'sort_order' => $this->sort_order,
        ];

        if ($this->license_type === 'duration') {
            $data['duration_days'] = $this->duration_days;
            $data['credits_amount'] = null;
        } else {
            $data['duration_days'] = null;
            $data['credits_amount'] = $this->credits_amount;
        }

        if ($this->isEditing) {
            LicensePlan::find($this->editingId)->update($data);
        } else {
            LicensePlan::create($data);
        }

        $this->closeModal();
    }

    public function toggleActive(int $id): void
    {
        $plan = LicensePlan::findOrFail($id);
        $plan->update(['is_active' => !$plan->is_active]);
    }

    public function delete(int $id): void
    {
        LicensePlan::findOrFail($id)->delete();
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->resetForm();
    }

    protected function resetForm(): void
    {
        $this->name = '';
        $this->product_name = '';
        $this->license_type = 'duration';
        $this->duration_days = 30;
        $this->credits_amount = null;
        $this->price = 0;
        $this->max_activations = 1;
        $this->description = '';
        $this->features = '';
        $this->is_active = true;
        $this->sort_order = 0;
        $this->editingId = null;
        $this->resetValidation();
    }

    public function render()
    {
        $plans = LicensePlan::orderBy('sort_order')->orderBy('duration_days')->get();

        return view('livewire.admin.license-plans', [
            'plans' => $plans,
        ]);
    }
}
