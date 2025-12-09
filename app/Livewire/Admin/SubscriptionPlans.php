<?php

namespace App\Livewire\Admin;

use App\Models\SubscriptionPlan;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.app')]
#[Title('Subscription Plans')]
class SubscriptionPlans extends Component
{
    public bool $showModal = false;
    public bool $isEditing = false;
    public ?int $editingId = null;

    public string $name = '';
    public int $duration_days = 30;
    public int $price = 0;
    public string $description = '';
    public bool $is_active = true;
    public int $sort_order = 0;

    protected function rules(): array
    {
        return [
            'name' => 'required|min:2|max:255',
            'duration_days' => 'required|integer|min:1',
            'price' => 'required|integer|min:0',
            'description' => 'nullable|max:500',
            'is_active' => 'boolean',
            'sort_order' => 'integer|min:0',
        ];
    }

    public function openCreateModal(): void
    {
        $this->resetForm();
        $this->showModal = true;
        $this->isEditing = false;
    }

    public function openEditModal(int $id): void
    {
        $plan = SubscriptionPlan::findOrFail($id);
        
        $this->editingId = $plan->id;
        $this->name = $plan->name;
        $this->duration_days = $plan->duration_days;
        $this->price = $plan->price;
        $this->description = $plan->description ?? '';
        $this->is_active = $plan->is_active;
        $this->sort_order = $plan->sort_order;
        
        $this->showModal = true;
        $this->isEditing = true;
    }

    public function save(): void
    {
        $this->validate();

        $data = [
            'name' => $this->name,
            'duration_days' => $this->duration_days,
            'price' => $this->price,
            'description' => $this->description ?: null,
            'is_active' => $this->is_active,
            'sort_order' => $this->sort_order,
        ];

        if ($this->isEditing) {
            SubscriptionPlan::find($this->editingId)->update($data);
        } else {
            SubscriptionPlan::create($data);
        }

        $this->closeModal();
    }

    public function toggleActive(int $id): void
    {
        $plan = SubscriptionPlan::findOrFail($id);
        $plan->update(['is_active' => !$plan->is_active]);
    }

    public function delete(int $id): void
    {
        SubscriptionPlan::findOrFail($id)->delete();
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->resetForm();
    }

    protected function resetForm(): void
    {
        $this->name = '';
        $this->duration_days = 30;
        $this->price = 0;
        $this->description = '';
        $this->is_active = true;
        $this->sort_order = 0;
        $this->editingId = null;
        $this->resetValidation();
    }

    public function render()
    {
        $plans = SubscriptionPlan::orderBy('sort_order')->orderBy('duration_days')->get();

        return view('livewire.admin.subscription-plans', [
            'plans' => $plans,
        ]);
    }
}
