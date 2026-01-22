<?php

namespace App\Livewire\Admin;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
#[Title('User Management')]
class Users extends Component
{
    use WithPagination;

    #[Url(except: '')]
    public string $search = '';
    
    #[Url(except: '')]
    public string $roleFilter = '';
    
    #[Url(except: '')]
    public string $subscriptionFilter = '';
    
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

    #[Validate('boolean')]
    public bool $is_subscribed = false;

    public int $subscription_days = 30;

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedRoleFilter(): void
    {
        $this->resetPage();
    }

    public function updatedSubscriptionFilter(): void
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
        $this->is_subscribed = $user->is_subscribed;
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
            'is_subscribed' => 'boolean',
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
            'is_subscribed' => $this->is_subscribed,
        ];

        if ($this->password) {
            $data['password'] = Hash::make($this->password);
        }

        if ($this->isEditing) {
            $user = User::find($this->editingUserId);
            
            if ($this->is_subscribed && !$user->is_subscribed) {
                $data['subscription_expires_at'] = Carbon::now()->addDays($this->subscription_days);
            }
            
            $user->update($data);
        } else {
            if ($this->is_subscribed) {
                $data['subscription_expires_at'] = Carbon::now()->addDays($this->subscription_days);
            }
            User::create($data);
        }

        $this->closeModal();
    }

    public function toggleActive(int $userId): void
    {
        $user = User::findOrFail($userId);
        
        if ($user->id === auth()->id()) {
            return;
        }

        $user->update(['is_active' => !$user->is_active]);
    }

    public function toggleSubscription(int $userId): void
    {
        $user = User::findOrFail($userId);
        
        if ($user->is_subscribed) {
            $user->update([
                'is_subscribed' => false,
                'subscription_expires_at' => null,
            ]);
        } else {
            $user->update([
                'is_subscribed' => true,
                'subscription_expires_at' => Carbon::now()->addDays(30),
            ]);
        }
    }

    public function extendSubscription(int $userId, int $days): void
    {
        $user = User::findOrFail($userId);
        
        $baseDate = $user->subscription_expires_at && $user->subscription_expires_at->isFuture() 
            ? $user->subscription_expires_at->copy()
            : Carbon::now();
        
        $user->update([
            'is_subscribed' => true,
            'subscription_expires_at' => $baseDate->addDays($days),
        ]);
    }

    public function addCredits(int $userId, int $credits): void
    {
        if ($credits <= 0) {
            return;
        }

        $user = User::findOrFail($userId);
        $user->increment('bonus_credits', $credits);
    }

    public function delete(int $userId): void
    {
        $user = User::findOrFail($userId);
        
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
        $this->is_subscribed = false;
        $this->subscription_days = 30;
        $this->editingUserId = null;
        $this->resetValidation();
    }

    #[Computed]
    public function users()
    {
        return User::query()
            ->select(['id', 'name', 'email', 'role', 'is_active', 'is_subscribed', 'subscription_expires_at', 'bonus_credits', 'created_at'])
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('name', 'like', "%{$this->search}%")
                      ->orWhere('email', 'like', "%{$this->search}%");
                });
            })
            ->when($this->roleFilter, function ($query) {
                $query->where('role', $this->roleFilter);
            })
            ->when($this->subscriptionFilter !== '', function ($query) {
                if ($this->subscriptionFilter === 'subscribed') {
                    $query->where('is_subscribed', true);
                } elseif ($this->subscriptionFilter === 'free') {
                    $query->where('is_subscribed', false);
                }
            })
            ->latest()
            ->paginate(10);
    }

    public function render()
    {
        return view('livewire.admin.users', [
            'users' => $this->users,
        ]);
    }
}

