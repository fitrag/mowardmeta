<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div class="section-header mb-0">
            <h1>Subscription Plans</h1>
            <p>Manage pricing plans and subscription tiers</p>
        </div>
        <button wire:click="openCreateModal" class="btn-primary">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Add Plan
        </button>
    </div>

    <!-- Plans Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        @forelse($plans as $plan)
            <div class="card p-0 overflow-hidden transition-all hover:border-[var(--border-color-strong)] {{ !$plan->is_active ? 'opacity-60' : '' }}">
                <!-- Card Header -->
                <div class="p-5 relative">
                    @if(!$plan->is_active)
                        <span class="absolute top-4 right-4 badge badge-danger">Inactive</span>
                    @elseif($plan->sort_order === 0)
                        <span class="absolute top-4 right-4 badge badge-accent">Popular</span>
                    @endif
                    
                    <div class="mb-4">
                        <h3 class="text-base font-semibold" style="color: var(--text-primary);">{{ $plan->name }}</h3>
                        <p class="text-xs mt-0.5" style="color: var(--text-muted);">{{ $plan->duration_label }}</p>
                    </div>
                    
                    <div class="flex items-baseline gap-1">
                        <span class="text-2xl font-bold" style="color: var(--accent);">{{ $plan->formatted_price }}</span>
                        <span class="text-xs" style="color: var(--text-muted);">/ {{ $plan->duration_days }} days</span>
                    </div>
                    
                    @if($plan->description)
                        <p class="text-xs mt-3 leading-relaxed" style="color: var(--text-secondary);">{{ $plan->description }}</p>
                    @endif
                </div>
                
                <!-- Features Preview -->
                <div class="px-5 pb-4">
                    <div class="space-y-2">
                        <div class="flex items-center gap-2 text-xs" style="color: var(--text-secondary);">
                            <svg class="w-3.5 h-3.5" style="color: var(--success);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            Unlimited generations
                        </div>
                        <div class="flex items-center gap-2 text-xs" style="color: var(--text-secondary);">
                            <svg class="w-3.5 h-3.5" style="color: var(--success);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            Full AI image analysis
                        </div>
                        <div class="flex items-center gap-2 text-xs" style="color: var(--text-secondary);">
                            <svg class="w-3.5 h-3.5" style="color: var(--success);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            Priority support
                        </div>
                    </div>
                </div>
                
                <!-- Actions -->
                <div class="px-5 py-3 flex items-center gap-2" style="border-top: 1px solid var(--border-color); background-color: var(--bg-muted);">
                    <button
                        wire:click="openEditModal({{ $plan->id }})"
                        class="flex-1 btn-ghost text-xs justify-center"
                    >
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        </svg>
                        Edit
                    </button>
                    <button
                        wire:click="toggleActive({{ $plan->id }})"
                        class="flex-1 text-xs font-medium rounded-lg py-2 transition-colors {{ $plan->is_active ? 'badge-warning' : 'badge-success' }}"
                    >
                        {{ $plan->is_active ? 'Deactivate' : 'Activate' }}
                    </button>
                    <button
                        wire:click="delete({{ $plan->id }})"
                        wire:confirm="Are you sure you want to delete this plan?"
                        class="p-2 rounded-lg transition-colors hover:bg-red-500/10"
                        style="color: var(--danger);"
                        title="Delete"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                    </button>
                </div>
            </div>
        @empty
            <div class="col-span-full">
                <div class="card py-12">
                    <div class="empty-state">
                        <div class="empty-state-icon">
                            <svg class="w-10 h-10" style="color: var(--text-muted);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <h3>No subscription plans yet</h3>
                        <p>Create your first plan to start accepting subscriptions</p>
                        <button wire:click="openCreateModal" class="btn-primary">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                            Add First Plan
                        </button>
                    </div>
                </div>
            </div>
        @endforelse
    </div>

    <!-- Modal -->
    @if($showModal)
        <div class="modal-overlay">
            <div class="modal-backdrop" wire:click="closeModal"></div>
            <div class="modal-content max-w-md">
                <h2 class="text-base font-semibold mb-5" style="color: var(--text-primary);">
                    {{ $isEditing ? 'Edit Plan' : 'Add New Plan' }}
                </h2>
                <form wire:submit="save" class="space-y-4">
                    <div>
                        <label for="name" class="label">Plan Name</label>
                        <input type="text" id="name" wire:model="name" class="input" placeholder="e.g. 1 Month Pro">
                        @error('name') <p class="mt-1 text-xs" style="color: var(--danger);">{{ $message }}</p> @enderror
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label for="duration_days" class="label">Duration (days)</label>
                            <input type="number" id="duration_days" wire:model="duration_days" class="input" min="1" placeholder="30">
                            @error('duration_days') <p class="mt-1 text-xs" style="color: var(--danger);">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="price" class="label">Price (Rp)</label>
                            <input type="number" id="price" wire:model="price" class="input" min="0" placeholder="50000">
                            @error('price') <p class="mt-1 text-xs" style="color: var(--danger);">{{ $message }}</p> @enderror
                        </div>
                    </div>
                    <div>
                        <label for="description" class="label">Description (optional)</label>
                        <textarea id="description" wire:model="description" class="input" rows="2" placeholder="Features included..."></textarea>
                        @error('description') <p class="mt-1 text-xs" style="color: var(--danger);">{{ $message }}</p> @enderror
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label for="sort_order" class="label">Sort Order</label>
                            <input type="number" id="sort_order" wire:model="sort_order" class="input" min="0">
                        </div>
                        <div class="flex items-end pb-1">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" wire:model="is_active" class="w-4 h-4 rounded" style="border-color: var(--border-color); background-color: var(--bg-input);">
                                <span class="text-sm" style="color: var(--text-primary);">Active</span>
                            </label>
                        </div>
                    </div>
                    <div class="flex gap-3 pt-2">
                        <button type="button" wire:click="closeModal" class="btn-secondary flex-1">Cancel</button>
                        <button type="submit" class="btn-primary flex-1">{{ $isEditing ? 'Update Plan' : 'Create Plan' }}</button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
