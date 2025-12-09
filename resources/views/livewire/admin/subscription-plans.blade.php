<div class="space-y-8">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl lg:text-3xl font-bold" style="color: var(--text-primary);">Subscription Plans</h1>
            <p class="mt-1" style="color: var(--text-secondary);">Manage subscription pricing and durations</p>
        </div>

        <button wire:click="openCreateModal" class="btn-primary">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Add Plan
        </button>
    </div>

    <!-- Plans Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($plans as $plan)
            <div class="card relative {{ !$plan->is_active ? 'opacity-60' : '' }}">
                @if(!$plan->is_active)
                    <span class="absolute top-3 right-3 px-2 py-1 rounded text-xs bg-red-500/20 text-red-500">Inactive</span>
                @endif
                
                <div class="mb-4">
                    <h3 class="text-xl font-bold" style="color: var(--text-primary);">{{ $plan->name }}</h3>
                    <p class="text-sm" style="color: var(--text-secondary);">{{ $plan->duration_label }}</p>
                </div>
                
                <div class="mb-4">
                    <span class="text-3xl font-bold text-primary-500">{{ $plan->formatted_price }}</span>
                </div>
                
                @if($plan->description)
                    <p class="text-sm mb-4" style="color: var(--text-secondary);">{{ $plan->description }}</p>
                @endif
                
                <div class="flex items-center gap-2 pt-4" style="border-top: 1px solid var(--border-color);">
                    <button 
                        wire:click="openEditModal({{ $plan->id }})"
                        class="flex-1 py-2 rounded-lg text-sm font-medium transition-colors"
                        style="background-color: var(--bg-hover); color: var(--text-primary);"
                    >
                        Edit
                    </button>
                    <button 
                        wire:click="toggleActive({{ $plan->id }})"
                        class="flex-1 py-2 rounded-lg text-sm font-medium transition-colors {{ $plan->is_active ? 'bg-amber-500/20 text-amber-600' : 'bg-emerald-500/20 text-emerald-600' }}"
                    >
                        {{ $plan->is_active ? 'Deactivate' : 'Activate' }}
                    </button>
                    <button 
                        wire:click="delete({{ $plan->id }})"
                        wire:confirm="Are you sure you want to delete this plan?"
                        class="p-2 hover:bg-red-500/20 rounded-lg text-red-500 transition-colors"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                    </button>
                </div>
            </div>
        @empty
            <div class="col-span-full">
                <div class="card text-center py-12">
                    <svg class="w-12 h-12 mx-auto mb-4" style="color: var(--text-muted);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <p class="font-medium" style="color: var(--text-primary);">No subscription plans yet</p>
                    <p class="text-sm mt-1" style="color: var(--text-secondary);">Create your first plan to start accepting subscriptions</p>
                </div>
            </div>
        @endforelse
    </div>

    <!-- Modal -->
    @if($showModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" wire:click="closeModal"></div>
            
            <div class="relative rounded-2xl w-full max-w-md p-6 animate-fade-in" style="background-color: var(--bg-secondary); border: 1px solid var(--border-color);">
                <h2 class="text-xl font-bold mb-6" style="color: var(--text-primary);">
                    {{ $isEditing ? 'Edit Plan' : 'Add New Plan' }}
                </h2>

                <form wire:submit="save" class="space-y-4">
                    <div>
                        <label for="name" class="label">Plan Name</label>
                        <input type="text" id="name" wire:model="name" class="input" placeholder="e.g. 1 Bulan">
                        @error('name') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label for="duration_days" class="label">Duration (days)</label>
                            <input type="number" id="duration_days" wire:model="duration_days" class="input" min="1" placeholder="30">
                            @error('duration_days') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="price" class="label">Price (Rp)</label>
                            <input type="number" id="price" wire:model="price" class="input" min="0" placeholder="50000">
                            @error('price') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div>
                        <label for="description" class="label">Description (optional)</label>
                        <textarea id="description" wire:model="description" class="input" rows="2" placeholder="Features included..."></textarea>
                        @error('description') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label for="sort_order" class="label">Sort Order</label>
                            <input type="number" id="sort_order" wire:model="sort_order" class="input" min="0">
                        </div>
                        <div class="flex items-end pb-1">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" wire:model="is_active" class="w-4 h-4 rounded text-primary-500 focus:ring-primary-500/50" style="border-color: var(--border-color); background-color: var(--bg-input);">
                                <span class="text-sm" style="color: var(--text-primary);">Active</span>
                            </label>
                        </div>
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
