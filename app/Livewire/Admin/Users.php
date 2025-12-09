<?php

namespace App\Livewire\Admin;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
#[Title('User Management')]
class Users extends Component
{
    use WithPagination;

    public string $search = '';
    public string $roleFilter = '';
    public bool $showModal = false;
    public bool $isEditing = false;
    public ?int $editingUserId = null;

    #[Validate('required|min:2|max:255')]
    public string $name = '';

    #[Validate('required|email')]
    public string $email = '';

    public string $password = '';

    #[Validate('required|in:admin,user')]
    public string $role = 'user';

    #[Validate('boolean')]
    public bool $is_active = true;

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedRoleFilter(): void
    {
        $this->resetPage();
    }

    public function openCreateModal(): void
    {
        $this->resetForm();
        $this->showModal = true;
        $this->isEditing = false;
    }

    public function openEditModal(int $userId): void
    {
        $user = User::findOrFail($userId);
        
        $this->editingUserId = $user->id;
        $this->name = $user->name;
        $this->email = $user->email;
        $this->role = $user->role;
        $this->is_active = $user->is_active;
        $this->password = '';
        
        $this->showModal = true;
        $this->isEditing = true;
    }

    public function save(): void
    {
        $rules = [
            'name' => 'required|min:2|max:255',
            'email' => 'required|email|unique:users,email' . ($this->isEditing ? ',' . $this->editingUserId : ''),
            'role' => 'required|in:admin,user',
            'is_active' => 'boolean',
        ];

        if (!$this->isEditing || $this->password) {
            $rules['password'] = 'required|min:8';
        }

        $this->validate($rules);

        $data = [
            'name' => $this->name,
            'email' => $this->email,
            'role' => $this->role,
            'is_active' => $this->is_active,
        ];

        if ($this->password) {
            $data['password'] = Hash::make($this->password);
        }

        if ($this->isEditing) {
            User::find($this->editingUserId)->update($data);
        } else {
            User::create($data);
        }

        $this->closeModal();
    }

    public function toggleActive(int $userId): void
    {
        $user = User::findOrFail($userId);
        
        // Prevent deactivating yourself
        if ($user->id === auth()->id()) {
            return;
        }

        $user->update(['is_active' => !$user->is_active]);
    }

    public function delete(int $userId): void
    {
        $user = User::findOrFail($userId);
        
        // Prevent deleting yourself
        if ($user->id === auth()->id()) {
            return;
        }

        $user->delete();
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->resetForm();
    }

    protected function resetForm(): void
    {
        $this->name = '';
        $this->email = '';
        $this->password = '';
        $this->role = 'user';
        $this->is_active = true;
        $this->editingUserId = null;
        $this->resetValidation();
    }

    public function render()
    {
        $users = User::query()
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('name', 'like', "%{$this->search}%")
                      ->orWhere('email', 'like', "%{$this->search}%");
                });
            })
            ->when($this->roleFilter, function ($query) {
                $query->where('role', $this->roleFilter);
            })
            ->latest()
            ->paginate(10);

        return view('livewire.admin.users', [
            'users' => $users,
        ]);
    }
}
