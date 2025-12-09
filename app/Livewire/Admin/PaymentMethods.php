<?php

namespace App\Livewire\Admin;

use App\Models\PaymentMethod;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.app')]
#[Title('Payment Methods')]
class PaymentMethods extends Component
{
    public bool $showModal = false;
    public bool $isEditing = false;
    public ?int $editingId = null;

    public string $name = '';
    public string $account_number = '';
    public string $account_holder_name = '';
    public string $description = '';
    public bool $is_active = true;
    public int $sort_order = 0;

    protected function rules(): array
    {
        return [
            'name' => 'required|min:2|max:255',
            'account_number' => 'required|min:5|max:50',
            'account_holder_name' => 'required|min:2|max:255',
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
        $method = PaymentMethod::findOrFail($id);
        
        $this->editingId = $method->id;
        $this->name = $method->name;
        $this->account_number = $method->account_number;
        $this->account_holder_name = $method->account_holder_name;
        $this->description = $method->description ?? '';
        $this->is_active = $method->is_active;
        $this->sort_order = $method->sort_order;
        
        $this->showModal = true;
        $this->isEditing = true;
    }

    public function save(): void
    {
        $this->validate();

        $data = [
            'name' => $this->name,
            'account_number' => $this->account_number,
            'account_holder_name' => $this->account_holder_name,
            'description' => $this->description ?: null,
            'is_active' => $this->is_active,
            'sort_order' => $this->sort_order,
        ];

        if ($this->isEditing) {
            PaymentMethod::find($this->editingId)->update($data);
        } else {
            PaymentMethod::create($data);
        }

        $this->closeModal();
    }

    public function toggleActive(int $id): void
    {
        $method = PaymentMethod::findOrFail($id);
        $method->update(['is_active' => !$method->is_active]);
    }

    public function delete(int $id): void
    {
        PaymentMethod::findOrFail($id)->delete();
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->resetForm();
    }

    protected function resetForm(): void
    {
        $this->name = '';
        $this->account_number = '';
        $this->account_holder_name = '';
        $this->description = '';
        $this->is_active = true;
        $this->sort_order = 0;
        $this->editingId = null;
        $this->resetValidation();
    }

    public function render()
    {
        $methods = PaymentMethod::orderBy('sort_order')->orderBy('name')->get();

        return view('livewire.admin.payment-methods', [
            'methods' => $methods,
        ]);
    }
}
