<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold" style="color: var(--text-primary);">License Plans</h1>
            <p class="mt-1 text-sm" style="color: var(--text-secondary);">Manage license plans for your products</p>
        </div>
        <button wire:click="openCreateModal" class="btn-primary inline-flex items-center gap-2">
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
                    <span class="absolute top-3 right-3 px-2 py-1 text-xs rounded bg-red-500/20 text-red-500">Inactive</span>
                @else
                    <span class="absolute top-3 right-3 px-2 py-1 text-xs rounded {{ $plan->license_type === 'credits' ? 'bg-purple-500/20 text-purple-500' : 'bg-blue-500/20 text-blue-500' }}">
                        {{ $plan->license_type_label }}
                    </span>
                @endif
                
                <div class="mb-4">
                    <h3 class="text-lg font-bold" style="color: var(--text-primary);">{{ $plan->name }}</h3>
                    <p class="text-sm" style="color: var(--text-secondary);">{{ $plan->product_name }}</p>
                </div>

                <div class="mb-4">
                    <span class="text-3xl font-bold text-primary-500">{{ $plan->formatted_price }}</span>
                    <span class="text-sm" style="color: var(--text-secondary);">/ {{ $plan->duration_label }}</span>
                </div>

                <div class="space-y-2 mb-4 text-sm" style="color: var(--text-secondary);">
                    <div class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        {{ $plan->max_activations }} activation(s)
                    </div>
                    @if($plan->license_type === 'credits')
                        <div class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            {{ $plan->credits_amount }} credits
                        </div>
                    @else
                        <div class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            {{ $plan->duration_days }} days validity
                        </div>
                    @endif
                </div>

                @if($plan->features)
                    <ul class="space-y-1 mb-4 text-sm" style="color: var(--text-secondary);">
                        @foreach($plan->features as $feature)
                            <li class="flex items-center gap-2">
                                <svg class="w-4 h-4 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                                {{ $feature }}
                            </li>
                        @endforeach
                    </ul>
                @endif

                <div class="flex items-center gap-2 pt-4 border-t" style="border-color: var(--border-color);">
                    <button wire:click="openEditModal({{ $plan->id }})" class="flex-1 btn-secondary text-sm">Edit</button>
                    <button wire:click="toggleActive({{ $plan->id }})" class="btn-secondary text-sm">
                        {{ $plan->is_active ? 'Disable' : 'Enable' }}
                    </button>
                    <button wire:click="delete({{ $plan->id }})" wire:confirm="Are you sure you want to delete this plan?" class="p-2 text-red-500 hover:bg-red-500/10 rounded-lg transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                    </button>
                </div>
            </div>
        @empty
            <div class="col-span-full text-center py-12">
                <p style="color: var(--text-secondary);">No license plans yet. Create your first plan!</p>
            </div>
        @endforelse
    </div>

    <!-- Modal -->
    @if($showModal)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-modal="true">
            <div class="flex items-center justify-center min-h-screen p-4">
                <div class="fixed inset-0 bg-black/50" wire:click="closeModal"></div>
                
                <div class="relative w-full max-w-lg card">
                    <h2 class="text-xl font-bold mb-6" style="color: var(--text-primary);">
                        {{ $isEditing ? 'Edit Plan' : 'Create Plan' }}
                    </h2>

                    <form wire:submit="save" class="space-y-4">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium mb-1" style="color: var(--text-secondary);">Plan Name</label>
                                <input type="text" wire:model="name" class="input w-full" placeholder="e.g. Basic">
                                @error('name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium mb-1" style="color: var(--text-secondary);">Product Name</label>
                                <input type="text" wire:model="product_name" class="input w-full" placeholder="e.g. MetaGen Pro">
                                @error('product_name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
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
                                        <div>
                                            <span class="text-sm font-medium {{ $license_type === 'duration' ? 'text-blue-500' : '' }}" style="{{ $license_type !== 'duration' ? 'color: var(--text-primary)' : '' }}">Duration</span>
                                            <p class="text-xs" style="color: var(--text-muted);">Time-based license</p>
                                        </div>
                                    </div>
                                </label>
                                <label class="relative flex items-center p-3 rounded-lg border cursor-pointer transition-colors {{ $license_type === 'credits' ? 'border-purple-500 bg-purple-500/10' : 'border-[var(--border-color)] hover:bg-[var(--bg-hover)]' }}">
                                    <input type="radio" wire:model.live="license_type" value="credits" class="sr-only">
                                    <div class="flex items-center gap-2">
                                        <svg class="w-5 h-5 {{ $license_type === 'credits' ? 'text-purple-500' : '' }}" style="{{ $license_type !== 'credits' ? 'color: var(--text-secondary)' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                                        </svg>
                                        <div>
                                            <span class="text-sm font-medium {{ $license_type === 'credits' ? 'text-purple-500' : '' }}" style="{{ $license_type !== 'credits' ? 'color: var(--text-primary)' : '' }}">Credits</span>
                                            <p class="text-xs" style="color: var(--text-muted);">Usage-based license</p>
                                        </div>
                                    </div>
                                </label>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            @if($license_type === 'duration')
                                <div>
                                    <label class="block text-sm font-medium mb-1" style="color: var(--text-secondary);">Duration (days)</label>
                                    <input type="number" wire:model="duration_days" class="input w-full" min="1">
                                    @error('duration_days') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>
                            @else
                                <div>
                                    <label class="block text-sm font-medium mb-1" style="color: var(--text-secondary);">Credits Amount</label>
                                    <input type="number" wire:model="credits_amount" class="input w-full" min="1" placeholder="e.g. 100">
                                    @error('credits_amount') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>
                            @endif
                            <div>
                                <label class="block text-sm font-medium mb-1" style="color: var(--text-secondary);">Price (Rp)</label>
                                <input type="number" wire:model="price" class="input w-full" min="0">
                                @error('price') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium mb-1" style="color: var(--text-secondary);">Max Activations</label>
                                <input type="number" wire:model="max_activations" class="input w-full" min="1">
                                @error('max_activations') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium mb-1" style="color: var(--text-secondary);">Sort Order</label>
                                <input type="number" wire:model="sort_order" class="input w-full" min="0">
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-1" style="color: var(--text-secondary);">Description</label>
                            <textarea wire:model="description" class="input w-full" rows="2" placeholder="Brief description..."></textarea>
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-1" style="color: var(--text-secondary);">Features (one per line)</label>
                            <textarea wire:model="features" class="input w-full" rows="3" placeholder="Feature 1&#10;Feature 2&#10;Feature 3"></textarea>
                        </div>

                        <div class="flex items-center gap-2">
                            <input type="checkbox" wire:model="is_active" id="is_active" class="rounded">
                            <label for="is_active" class="text-sm" style="color: var(--text-secondary);">Active</label>
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
