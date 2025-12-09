<div class="space-y-8">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl lg:text-3xl font-bold" style="color: var(--text-primary);">User Management</h1>
            <p class="mt-1" style="color: var(--text-secondary);">Manage user accounts and permissions</p>
        </div>

        <button wire:click="openCreateModal" class="btn-primary">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Add User
        </button>
    </div>

    <!-- Filters -->
    <div class="card">
        <div class="flex flex-col sm:flex-row gap-4">
            <div class="flex-1 relative">
                <svg class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5" style="color: var(--text-secondary);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <input 
                    type="text" 
                    wire:model.live.debounce.300ms="search"
                    placeholder="Search users..."
                    class="input pl-12"
                >
            </div>
            <select wire:model.live="roleFilter" class="input sm:w-48">
                <option value="">All Roles</option>
                <option value="admin">Admin</option>
                <option value="user">User</option>
            </select>
        </div>
    </div>

    <!-- Table -->
    <div class="card overflow-hidden p-0">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead style="background-color: var(--bg-hover); border-bottom: 1px solid var(--border-color);">
                    <tr>
                        <th class="px-4 py-3 text-left text-sm font-medium" style="color: var(--text-secondary);">User</th>
                        <th class="px-4 py-3 text-left text-sm font-medium" style="color: var(--text-secondary);">Role</th>
                        <th class="px-4 py-3 text-left text-sm font-medium hidden md:table-cell" style="color: var(--text-secondary);">Status</th>
                        <th class="px-4 py-3 text-left text-sm font-medium hidden lg:table-cell" style="color: var(--text-secondary);">Joined</th>
                        <th class="px-4 py-3 text-right text-sm font-medium" style="color: var(--text-secondary);">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $user)
                        <tr class="transition-colors" style="border-bottom: 1px solid var(--border-color);" onmouseover="this.style.backgroundColor='var(--bg-hover)'" onmouseout="this.style.backgroundColor='transparent'">
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-full bg-gradient-to-br from-primary-500 to-accent-cyan flex items-center justify-center font-semibold text-sm text-white">
                                        {{ strtoupper(substr($user->name, 0, 1)) }}
                                    </div>
                                    <div>
                                        <p class="font-medium" style="color: var(--text-primary);">{{ $user->name }}</p>
                                        <p class="text-sm" style="color: var(--text-secondary);">{{ $user->email }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                <span class="px-2 py-1 rounded text-xs {{ $user->role === 'admin' ? 'bg-primary-500/20 text-primary-600' : '' }}" style="{{ $user->role !== 'admin' ? 'background-color: var(--bg-hover); color: var(--text-secondary);' : '' }}">
                                    {{ ucfirst($user->role) }}
                                </span>
                            </td>
                            <td class="px-4 py-3 hidden md:table-cell">
                                <button 
                                    wire:click="toggleActive({{ $user->id }})"
                                    @if($user->id === auth()->id()) disabled @endif
                                    class="flex items-center gap-2 {{ $user->id === auth()->id() ? 'cursor-not-allowed opacity-50' : '' }}"
                                >
                                    @if($user->is_active)
                                        <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                                        <span class="text-sm text-emerald-600">Active</span>
                                    @else
                                        <span class="w-2 h-2 rounded-full bg-red-500"></span>
                                        <span class="text-sm text-red-600">Inactive</span>
                                    @endif
                                </button>
                            </td>
                            <td class="px-4 py-3 hidden lg:table-cell">
                                <span class="text-sm" style="color: var(--text-secondary);">{{ $user->created_at->format('M d, Y') }}</span>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <button 
                                        wire:click="openEditModal({{ $user->id }})"
                                        class="p-2 rounded-lg transition-colors"
                                        style="color: var(--text-secondary);"
                                        onmouseover="this.style.backgroundColor='var(--bg-hover)'"
                                        onmouseout="this.style.backgroundColor='transparent'"
                                    >
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                    </button>
                                    @if($user->id !== auth()->id())
                                        <button 
                                            wire:click="delete({{ $user->id }})"
                                            wire:confirm="Are you sure you want to delete this user?"
                                            class="p-2 hover:bg-red-500/20 rounded-lg text-red-500 transition-colors"
                                        >
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-8 text-center" style="color: var(--text-secondary);">
                                No users found
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

    <!-- Modal -->
    @if($showModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" wire:click="closeModal"></div>
            
            <div class="relative rounded-2xl w-full max-w-md p-6 animate-fade-in" style="background-color: var(--bg-secondary); border: 1px solid var(--border-color);">
                <h2 class="text-xl font-bold mb-6" style="color: var(--text-primary);">
                    {{ $isEditing ? 'Edit User' : 'Add New User' }}
                </h2>

                <form wire:submit="save" class="space-y-4">
                    <div>
                        <label for="name" class="label">Name</label>
                        <input type="text" id="name" wire:model="name" class="input">
                        @error('name') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="email" class="label">Email</label>
                        <input type="email" id="email" wire:model="email" class="input">
                        @error('email') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="password" class="label">
                            Password {{ $isEditing ? '(leave blank to keep current)' : '' }}
                        </label>
                        <input type="password" id="password" wire:model="password" class="input">
                        @error('password') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="role" class="label">Role</label>
                        <select id="role" wire:model="role" class="input">
                            <option value="user">User</option>
                            <option value="admin">Admin</option>
                        </select>
                        @error('role') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" wire:model="is_active" class="w-4 h-4 rounded text-primary-500 focus:ring-primary-500/50" style="border-color: var(--border-color); background-color: var(--bg-input);">
                            <span class="text-sm" style="color: var(--text-primary);">Active</span>
                        </label>
                    </div>

                    <div class="flex gap-3 pt-4">
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
