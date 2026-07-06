<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div class="section-header mb-0">
            <h1>User Management</h1>
            <p>Manage user accounts, permissions, and subscriptions</p>
        </div>

        <button wire:click="openCreateModal" class="btn-primary">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Add User
        </button>
    </div>

    <div class="card">
        <div class="flex flex-col sm:flex-row gap-3">
            <div class="flex-1 relative">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4" style="color: var(--text-muted);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <input 
                    type="text" 
                    wire:model.live.debounce.300ms="search"
                    placeholder="Search users..."
                    class="input pl-10"
                >
            </div>
            <select wire:model.live="roleFilter" class="input sm:w-36">
                <option value="">All Roles</option>
                <option value="admin">Admin</option>
                <option value="user">User</option>
            </select>
            <select wire:model.live="subscriptionFilter" class="input sm:w-40">
                <option value="">All Subscriptions</option>
                <option value="subscribed">Subscribed</option>
                <option value="free">Free</option>
            </select>
        </div>
    </div>

    <div class="card overflow-hidden p-0">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="table-header">
                    <tr>
                        <th class="px-4 py-2.5 text-left text-xs font-medium" style="color: var(--text-muted);">User</th>
                        <th class="px-4 py-2.5 text-left text-xs font-medium" style="color: var(--text-muted);">Role</th>
                        <th class="px-4 py-2.5 text-left text-xs font-medium hidden md:table-cell" style="color: var(--text-muted);">Status</th>
                        <th class="px-4 py-2.5 text-left text-xs font-medium hidden lg:table-cell" style="color: var(--text-muted);">Subscription</th>
                        <th class="px-4 py-2.5 text-left text-xs font-medium hidden xl:table-cell" style="color: var(--text-muted);">Credits</th>
                        <th class="px-4 py-2.5 text-right text-xs font-medium" style="color: var(--text-muted);">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $user)
                        <tr class="table-row">
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-3">
                                    <div class="avatar-sm flex-shrink-0">
                                        {{ strtoupper(substr($user->name, 0, 1)) }}
                                    </div>
                                    <div class="min-w-0">
                                        <p class="text-sm font-medium truncate" style="color: var(--text-primary);">{{ $user->name }}</p>
                                        <p class="text-xs truncate" style="color: var(--text-muted);">{{ $user->email }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                <span class="badge {{ $user->role === 'admin' ? 'badge-accent' : 'badge-neutral' }}">
                                    {{ ucfirst($user->role) }}
                                </span>
                            </td>
                            <td class="px-4 py-3 hidden md:table-cell">
                                <button 
                                    wire:click="toggleActive({{ $user->id }})"
                                    @if($user->id === auth()->id()) disabled @endif
                                    class="flex items-center gap-1.5 {{ $user->id === auth()->id() ? 'cursor-not-allowed opacity-50' : '' }}"
                                >
                                    <span class="w-1.5 h-1.5 rounded-full {{ $user->is_active ? 'bg-emerald-500' : 'bg-red-500' }}"></span>
                                    <span class="text-xs {{ $user->is_active ? 'text-emerald-500' : 'text-red-500' }}">{{ $user->is_active ? 'Active' : 'Inactive' }}</span>
                                </button>
                            </td>
                            <td class="px-4 py-3 hidden lg:table-cell">
                                @if($user->role === 'admin')
                                    <span class="badge badge-accent">Admin</span>
                                @elseif($user->isSubscribed())
                                    <div class="flex items-center gap-1.5">
                                        <span class="badge badge-success">Subscribed</span>
                                        @if($user->subscription_expires_at)
                                            <span class="text-xs" style="color: var(--text-muted);">
                                                until {{ $user->subscription_expires_at->format('d M Y') }}
                                            </span>
                                        @endif
                                    </div>
                                @else
                                    <span class="badge badge-neutral">Free</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 hidden xl:table-cell">
                                @if($user->role !== 'admin' && !$user->isSubscribed())
                                    <div class="flex items-center gap-1.5">
                                        <span class="badge badge-warning">{{ $user->bonus_credits }} credits</span>
                                        <button 
                                            wire:click="addCredits({{ $user->id }}, 5)"
                                            class="px-1.5 py-0.5 text-xs rounded font-medium badge-success hover:opacity-80 transition-opacity"
                                            title="Add 5 credits"
                                        >
                                            +5
                                        </button>
                                    </div>
                                @elseif($user->role !== 'admin')
                                    <span class="text-xs" style="color: var(--text-muted);">Unlimited</span>
                                @else
                                    <span class="text-xs" style="color: var(--text-muted);">—</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right">
                                <div class="flex items-center justify-end gap-1">
                                    @if($user->role !== 'admin')
                                        <button 
                                            wire:click="toggleSubscription({{ $user->id }})"
                                            class="p-1.5 rounded-md transition-colors {{ $user->is_subscribed ? 'text-amber-500 hover:bg-amber-500/10' : 'text-emerald-500 hover:bg-emerald-500/10' }}"
                                            title="{{ $user->is_subscribed ? 'Remove Subscription' : 'Add Subscription' }}"
                                        >
                                            @if($user->is_subscribed)
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                                                </svg>
                                            @else
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                </svg>
                                            @endif
                                        </button>
                                    @endif
                                    <button 
                                        wire:click="openEditModal({{ $user->id }})"
                                        class="p-1.5 rounded-md transition-colors btn-ghost"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                    </button>
                                    @if($user->id !== auth()->id())
                                        <button 
                                            wire:click="delete({{ $user->id }})"
                                            wire:confirm="Are you sure you want to delete this user?"
                                            class="p-1.5 rounded-md transition-colors text-red-500 hover:bg-red-500/10"
                                        >
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-10 text-center">
                                <div class="empty-state">
                                    <div class="empty-state-icon">
                                        <svg class="w-6 h-6" style="color: var(--text-muted);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                                        </svg>
                                    </div>
                                    <h3>No users found</h3>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($users->hasPages())
            <div class="px-4 py-3" style="border-top: 1px solid var(--border-color);">
                {{ $users->links() }}
            </div>
        @endif
    </div>

    @if($showModal)
        <div class="modal-overlay">
            <div class="modal-backdrop" wire:click="closeModal"></div>
            
            <div class="modal-content">
                <h2 class="text-base font-semibold mb-5" style="color: var(--text-primary);">
                    {{ $isEditing ? 'Edit User' : 'Add New User' }}
                </h2>

                <form wire:submit="save" class="space-y-4">
                    <div>
                        <label for="name" class="label">Name</label>
                        <input type="text" id="name" wire:model="name" class="input">
                        @error('name') <p class="mt-1 text-xs" style="color: var(--danger);">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="email" class="label">Email</label>
                        <input type="email" id="email" wire:model="email" class="input">
                        @error('email') <p class="mt-1 text-xs" style="color: var(--danger);">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="password" class="label">
                            Password {{ $isEditing ? '(leave blank to keep current)' : '' }}
                        </label>
                        <input type="password" id="password" wire:model="password" class="input">
                        @error('password') <p class="mt-1 text-xs" style="color: var(--danger);">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="role" class="label">Role</label>
                        <select id="role" wire:model="role" class="input">
                            <option value="user">User</option>
                            <option value="admin">Admin</option>
                        </select>
                        @error('role') <p class="mt-1 text-xs" style="color: var(--danger);">{{ $message }}</p> @enderror
                    </div>

                    <div class="flex items-center gap-6">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" wire:model="is_active" class="w-4 h-4 rounded transition-colors" style="border-color: var(--border-color); background-color: var(--bg-input);">
                            <span class="text-sm" style="color: var(--text-primary);">Active</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" wire:model="is_subscribed" class="w-4 h-4 rounded transition-colors" style="border-color: var(--border-color); background-color: var(--bg-input);">
                            <span class="text-sm" style="color: var(--text-primary);">Subscribed</span>
                        </label>
                    </div>

                    @if($is_subscribed)
                        <div class="p-3 rounded-lg" style="background-color: var(--bg-muted);">
                            <label for="subscription_days" class="label">Subscription Duration (days)</label>
                            <select id="subscription_days" wire:model="subscription_days" class="input">
                                <option value="30">30 days (1 Month)</option>
                                <option value="90">90 days (3 Months)</option>
                                <option value="180">180 days (6 Months)</option>
                                <option value="365">365 days (1 Year)</option>
                            </select>
                            <p class="text-xs mt-1.5" style="color: var(--text-muted);">
                                @if($isEditing)
                                    This will set the subscription duration from today when subscription is newly enabled.
                                @else
                                    Subscription will be active for this duration starting today.
                                @endif
                            </p>
                        </div>
                    @endif

                    <div class="flex gap-3 pt-3">
                        <button type="button" wire:click="closeModal" class="btn-secondary flex-1">
                            Cancel
                        </button>
                        <button type="submit" class="btn-primary flex-1">
                            {{ $isEditing ? 'Update' : 'Create' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
