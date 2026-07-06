<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold" style="color: var(--text-primary);">Licenses</h1>
            <p class="mt-1 text-sm" style="color: var(--text-secondary);">Manage product licenses</p>
        </div>
        <button wire:click="openCreateModal" class="btn-primary inline-flex items-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Create License
        </button>
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <div class="card">
            <p class="text-2xl font-bold" style="color: var(--text-primary);">{{ $stats['total'] }}</p>
            <p class="text-sm" style="color: var(--text-secondary);">Total</p>
        </div>
        <div class="card">
            <p class="text-2xl font-bold text-emerald-500">{{ $stats['active'] }}</p>
            <p class="text-sm" style="color: var(--text-secondary);">Active</p>
        </div>
        <div class="card">
            <p class="text-2xl font-bold text-blue-500">{{ $stats['duration'] }}</p>
            <p class="text-sm" style="color: var(--text-secondary);">Duration</p>
        </div>
        <div class="card">
            <p class="text-2xl font-bold text-purple-500">{{ $stats['credits'] }}</p>
            <p class="text-sm" style="color: var(--text-secondary);">Credits</p>
        </div>
    </div>

    <!-- Filters -->
    <div class="card">
        <div class="flex flex-col sm:flex-row gap-4">
            <div class="flex-1">
                <input type="text" wire:model.live.debounce.300ms="search" placeholder="Search by key, product, user..." class="input w-full">
            </div>
            <select wire:model.live="statusFilter" class="input w-full sm:w-40">
                <option value="">All Status</option>
                <option value="active">Active</option>
                <option value="pending">Pending</option>
                <option value="expired">Expired</option>
                <option value="revoked">Revoked</option>
            </select>
            <select wire:model.live="typeFilter" class="input w-full sm:w-40">
                <option value="">All Types</option>
                <option value="duration">Duration</option>
                <option value="credits">Credits</option>
            </select>
        </div>
    </div>

    <!-- Licenses Table -->
    <div class="card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr style="border-bottom: 1px solid var(--border-color);">
                        <th class="text-left p-4 text-sm font-medium" style="color: var(--text-secondary);">License Key</th>
                        <th class="text-left p-4 text-sm font-medium" style="color: var(--text-secondary);">User</th>
                        <th class="text-left p-4 text-sm font-medium" style="color: var(--text-secondary);">Product</th>
                        <th class="text-left p-4 text-sm font-medium" style="color: var(--text-secondary);">Type</th>
                        <th class="text-left p-4 text-sm font-medium" style="color: var(--text-secondary);">Status</th>
                        <th class="text-left p-4 text-sm font-medium" style="color: var(--text-secondary);">Validity</th>
                        <th class="text-right p-4 text-sm font-medium" style="color: var(--text-secondary);">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($licenses as $license)
                        <tr style="border-bottom: 1px solid var(--border-color);" class="hover:bg-[var(--bg-hover)]">
                            <td class="p-4">
                                <code class="text-sm font-mono" style="color: var(--text-primary);">{{ $license->license_key }}</code>
                            </td>
                            <td class="p-4">
                                <p class="font-medium" style="color: var(--text-primary);">{{ $license->user->name }}</p>
                                <p class="text-sm" style="color: var(--text-secondary);">{{ $license->user->email }}</p>
                            </td>
                            <td class="p-4" style="color: var(--text-primary);">
                                {{ $license->product_name }}
                                @if($license->domain)
                                    <p class="text-xs" style="color: var(--text-muted);">{{ $license->domain }}</p>
                                @endif
                            </td>
                            <td class="p-4">
                                @if($license->license_type === 'credits')
                                    <span class="px-2 py-1 text-xs rounded-full bg-purple-500/20 text-purple-500">Credits</span>
                                @else
                                    <span class="px-2 py-1 text-xs rounded-full bg-blue-500/20 text-blue-500">Duration</span>
                                @endif
                            </td>
                            <td class="p-4">
                                <span class="px-2 py-1 text-xs rounded-full bg-{{ $license->status_color }}-500/20 text-{{ $license->status_color }}-500">
                                    {{ $license->status_label }}
                                </span>
                            </td>
                            <td class="p-4 text-sm" style="color: var(--text-secondary);">
                                @if($license->license_type === 'credits')
                                    <span class="font-medium" style="color: var(--text-primary);">{{ $license->getCreditsRemaining() ?? '∞' }}</span> / {{ $license->credits_total ?? '∞' }}
                                    <p class="text-xs" style="color: var(--text-muted);">credits remaining</p>
                                @elseif($license->expires_at)
                                    {{ $license->expires_at->format('d M Y') }}
                                    @if($license->days_remaining !== null && $license->days_remaining > 0)
                                        <p class="text-xs" style="color: var(--text-muted);">{{ $license->days_remaining }} days left</p>
                                    @endif
                                @else
                                    <span style="color: var(--text-muted);">Lifetime</span>
                                @endif
                            </td>
                            <td class="p-4">
                                <div class="flex items-center justify-end gap-2">
                                    <button wire:click="openEditModal({{ $license->id }})" class="p-2 hover:bg-[var(--bg-hover)] rounded-lg transition-colors" title="Edit">
                                        <svg class="w-4 h-4" style="color: var(--text-secondary);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                    </button>
                                    <button wire:click="regenerateKey({{ $license->id }})" wire:confirm="Regenerate license key?" class="p-2 hover:bg-[var(--bg-hover)] rounded-lg transition-colors" title="Regenerate Key">
                                        <svg class="w-4 h-4" style="color: var(--text-secondary);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                        </svg>
                                    </button>
                                    @if($license->status === 'active')
                                        <button wire:click="revoke({{ $license->id }})" wire:confirm="Revoke this license?" class="p-2 hover:bg-red-500/10 rounded-lg transition-colors text-red-500" title="Revoke">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                                            </svg>
                                        </button>
                                    @endif
                                    <button wire:click="delete({{ $license->id }})" wire:confirm="Delete this license?" class="p-2 hover:bg-red-500/10 rounded-lg transition-colors text-red-500" title="Delete">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="p-8 text-center" style="color: var(--text-secondary);">
                                No licenses found
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <div class="p-4">
            {{ $licenses->links() }}
        </div>
    </div>

    <!-- Create/Edit Modal -->
    @if($showModal)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-modal="true">
            <div class="flex items-center justify-center min-h-screen p-4">
                <div class="fixed inset-0 bg-black/50" wire:click="closeModal"></div>
                
                <div class="relative w-full max-w-lg card">
                    <h2 class="text-xl font-bold mb-6" style="color: var(--text-primary);">
                        {{ $isEditing ? 'Edit License' : 'Create License' }}
                    </h2>

                    <form wire:submit="save" class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium mb-1" style="color: var(--text-secondary);">User</label>
                            <select wire:model="user_id" class="input w-full" {{ $isEditing ? 'disabled' : '' }}>
                                <option value="">Select user...</option>
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->email }})</option>
                                @endforeach
                            </select>
                            @error('user_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-1" style="color: var(--text-secondary);">Product Name</label>
                            <input type="text" wire:model="product_name" class="input w-full" placeholder="e.g. MetaGen Pro">
                            @error('product_name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-1" style="color: var(--text-secondary);">Domain (optional)</label>
                            <input type="text" wire:model="domain" class="input w-full" placeholder="e.g. example.com">
                            @error('domain') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium mb-1" style="color: var(--text-secondary);">Status</label>
                                <select wire:model="status" class="input w-full">
                                    <option value="active">Active</option>
                                    <option value="pending">Pending</option>
                                    <option value="expired">Expired</option>
                                    <option value="revoked">Revoked</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium mb-1" style="color: var(--text-secondary);">Max Activations</label>
                                <input type="number" wire:model="max_activations" class="input w-full" min="1">
                            </div>
                        </div>

                        <!-- License Type -->
                        <div>
                            <label class="block text-sm font-medium mb-2" style="color: var(--text-secondary);">License Type</label>
                            <div class="grid grid-cols-2 gap-3">
                                <label class="relative flex items-center p-3 rounded-lg border cursor-pointer transition-colors {{ $license_type === 'duration' ? 'border-blue-500 bg-blue-500/10' : 'border-[var(--border-color)] hover:bg-[var(--bg-hover)]' }}">
                                    <input type="radio" wire:model.live="license_type" value="duration" class="sr-only">
                                    <div class="flex items-center gap-2">
                                        <svg class="w-5 h-5 {{ $license_type === 'duration' ? 'text-blue-500' : '' }}" style="{{ $license_type !== 'duration' ? 'color: var(--text-secondary)' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        <span class="text-sm font-medium {{ $license_type === 'duration' ? 'text-blue-500' : '' }}" style="{{ $license_type !== 'duration' ? 'color: var(--text-primary)' : '' }}">Duration</span>
                                    </div>
                                </label>
                                <label class="relative flex items-center p-3 rounded-lg border cursor-pointer transition-colors {{ $license_type === 'credits' ? 'border-purple-500 bg-purple-500/10' : 'border-[var(--border-color)] hover:bg-[var(--bg-hover)]' }}">
                                    <input type="radio" wire:model.live="license_type" value="credits" class="sr-only">
                                    <div class="flex items-center gap-2">
                                        <svg class="w-5 h-5 {{ $license_type === 'credits' ? 'text-purple-500' : '' }}" style="{{ $license_type !== 'credits' ? 'color: var(--text-secondary)' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                                        </svg>
                                        <span class="text-sm font-medium {{ $license_type === 'credits' ? 'text-purple-500' : '' }}" style="{{ $license_type !== 'credits' ? 'color: var(--text-primary)' : '' }}">Credits</span>
                                    </div>
                                </label>
                            </div>
                        </div>

                        <!-- Duration-based fields -->
                        @if($license_type === 'duration')
                            <div>
                                <label class="block text-sm font-medium mb-1" style="color: var(--text-secondary);">Expires At (optional, leave empty for lifetime)</label>
                                <input type="date" wire:model="expires_at" class="input w-full">
                                @error('expires_at') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>
                        @endif

                        <!-- Credits-based fields -->
                        @if($license_type === 'credits')
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium mb-1" style="color: var(--text-secondary);">Total Credits</label>
                                    <input type="number" wire:model="credits_total" class="input w-full" min="1" placeholder="e.g. 100">
                                    @error('credits_total') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-medium mb-1" style="color: var(--text-secondary);">Credits Used</label>
                                    <input type="number" wire:model="credits_used" class="input w-full" min="0">
                                    @error('credits_used') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        @endif

                        <div>
                            <label class="block text-sm font-medium mb-1" style="color: var(--text-secondary);">Notes (optional)</label>
                            <textarea wire:model="notes" class="input w-full" rows="2"></textarea>
                        </div>

                        <div class="flex justify-end gap-3 pt-4">
                            <button type="button" wire:click="closeModal" class="btn-secondary">Cancel</button>
                            <button type="submit" class="btn-primary">{{ $isEditing ? 'Update' : 'Create' }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
</div>
