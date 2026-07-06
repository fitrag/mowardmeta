<div class="space-y-6">
    <!-- Flash Messages -->
    @if(session('success'))
        <div class="p-4 rounded-lg bg-emerald-500/20 text-emerald-500 border border-emerald-500/30">
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="p-4 rounded-lg bg-red-500/20 text-red-500 border border-red-500/30">
            {{ session('error') }}
        </div>
    @endif

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold" style="color: var(--text-primary);">Products</h1>
            <p class="mt-1 text-sm" style="color: var(--text-secondary);">Manage digital products</p>
        </div>
        <button wire:click="openCreateModal" class="btn-primary inline-flex items-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Add Product
        </button>
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <div class="card"><p class="text-2xl font-bold" style="color: var(--text-primary);">{{ $stats['total'] }}</p><p class="text-sm" style="color: var(--text-secondary);">Total</p></div>
        <div class="card"><p class="text-2xl font-bold text-emerald-500">{{ $stats['active'] }}</p><p class="text-sm" style="color: var(--text-secondary);">Active</p></div>
        <div class="card"><p class="text-2xl font-bold text-cyan-500">{{ $stats['free'] }}</p><p class="text-sm" style="color: var(--text-secondary);">Free</p></div>
        <div class="card"><p class="text-2xl font-bold text-purple-500">{{ $stats['paid'] }}</p><p class="text-sm" style="color: var(--text-secondary);">Paid</p></div>
    </div>

    <!-- Filters -->
    <div class="card">
        <div class="flex flex-col sm:flex-row gap-4">
            <input type="text" wire:model.live.debounce.300ms="search" placeholder="Search products..." class="input flex-1">
            <select wire:model.live="typeFilter" class="input w-full sm:w-40">
                <option value="">All Types</option>
                <option value="free">Free</option>
                <option value="paid">Paid</option>
            </select>
        </div>
    </div>

    <!-- Products Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($products as $product)
            <div class="card {{ !$product->is_active ? 'opacity-60' : '' }}">
                @if($product->thumbnail)
                    <img src="{{ asset($product->thumbnail) }}" alt="{{ $product->name }}" class="w-full h-40 object-cover rounded-lg mb-4">
                @else
                    <div class="w-full h-40 rounded-lg mb-4 flex items-center justify-center" style="background-color: var(--bg-hover);">
                        <svg class="w-12 h-12" style="color: var(--text-muted);" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                    </div>
                @endif

                <div class="flex items-start justify-between mb-2">
                    <h3 class="font-bold" style="color: var(--text-primary);">{{ $product->name }}</h3>
                    <span class="px-2 py-0.5 text-xs rounded {{ $product->type === 'free' ? 'bg-cyan-500/20 text-cyan-500' : 'bg-purple-500/20 text-purple-500' }}">
                        {{ ucfirst($product->type) }}
                    </span>
                </div>

                <p class="text-sm mb-3 line-clamp-2" style="color: var(--text-secondary);">{{ $product->short_description }}</p>

                <div class="flex items-center justify-between mb-4">
                    <div>
                        @if($product->hasDiscount())
                            <span class="text-lg font-bold text-emerald-500">{{ $product->formatted_price }}</span>
                            <span class="text-sm line-through ml-1" style="color: var(--text-muted);">{{ $product->formatted_original_price }}</span>
                        @else
                            <span class="text-lg font-bold {{ $product->isFree() ? 'text-cyan-500' : 'text-primary-500' }}">{{ $product->formatted_price }}</span>
                        @endif
                    </div>
                    <span class="text-xs" style="color: var(--text-muted);">v{{ $product->version }}</span>
                </div>

                <div class="flex items-center gap-2 text-xs mb-4" style="color: var(--text-muted);">
                    <span>{{ $product->download_count }} downloads</span>
                    @if($product->requires_license)<span class="px-1.5 py-0.5 rounded bg-amber-500/20 text-amber-500">License</span>@endif
                    @if($product->is_featured)<span class="px-1.5 py-0.5 rounded bg-primary-500/20 text-primary-500">Featured</span>@endif
                </div>

                <div class="flex items-center gap-2 pt-3 border-t" style="border-color: var(--border-color);">
                    <button wire:click="openEditModal({{ $product->id }})" class="flex-1 btn-secondary text-sm">Edit</button>
                    <button wire:click="toggleActive({{ $product->id }})" class="p-2 hover:bg-[var(--bg-hover)] rounded-lg" title="{{ $product->is_active ? 'Deactivate' : 'Activate' }}">
                        <svg class="w-4 h-4 {{ $product->is_active ? 'text-emerald-500' : 'text-gray-500' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                    </button>
                    <button wire:click="toggleFeatured({{ $product->id }})" class="p-2 hover:bg-[var(--bg-hover)] rounded-lg" title="Toggle Featured">
                        <svg class="w-4 h-4 {{ $product->is_featured ? 'text-amber-500' : '' }}" style="{{ !$product->is_featured ? 'color: var(--text-muted);' : '' }}" fill="{{ $product->is_featured ? 'currentColor' : 'none' }}" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/></svg>
                    </button>
                    <button wire:click="delete({{ $product->id }})" wire:confirm="Delete this product?" class="p-2 text-red-500 hover:bg-red-500/10 rounded-lg">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    </button>
                </div>
            </div>
        @empty
            <div class="col-span-full text-center py-12"><p style="color: var(--text-secondary);">No products found</p></div>
        @endforelse
    </div>

    <div>{{ $products->links() }}</div>

    <!-- Modal -->
    @if($showModal)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-modal="true">
            <div class="flex items-start justify-center min-h-screen p-4 pt-20">
                <div class="fixed inset-0 bg-black/50" wire:click="closeModal"></div>
                <div class="relative w-full max-w-2xl card max-h-[80vh] overflow-y-auto">
                    <h2 class="text-xl font-bold mb-6" style="color: var(--text-primary);">{{ $isEditing ? 'Edit Product' : 'Add Product' }}</h2>
                    <form wire:submit="save" class="space-y-4">
                        <div class="grid grid-cols-2 gap-4">
                            <div class="col-span-2 sm:col-span-1">
                                <label class="block text-sm font-medium mb-1" style="color: var(--text-secondary);">Name *</label>
                                <input type="text" wire:model.live="name" class="input w-full">
                                @error('name')<span class="text-red-500 text-xs">{{ $message }}</span>@enderror
                            </div>
                            <div class="col-span-2 sm:col-span-1">
                                <label class="block text-sm font-medium mb-1" style="color: var(--text-secondary);">Slug</label>
                                <input type="text" wire:model="slug" class="input w-full">
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-1" style="color: var(--text-secondary);">Short Description</label>
                            <input type="text" wire:model="short_description" class="input w-full" maxlength="500">
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-1" style="color: var(--text-secondary);">Description</label>
                            <textarea wire:model="description" class="input w-full" rows="3"></textarea>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium mb-1" style="color: var(--text-secondary);">Thumbnail</label>
                                <input type="file" wire:model="thumbnail" accept="image/*" class="input w-full text-sm">
                                <div wire:loading wire:target="thumbnail" class="text-xs text-primary-500 mt-1">Uploading...</div>
                                @error('thumbnail')<span class="text-red-500 text-xs">{{ $message }}</span>@enderror
                                @if($existingThumbnail && !$thumbnail)
                                    <img src="{{ asset($existingThumbnail) }}" class="mt-2 h-20 rounded">
                                @elseif($thumbnail)
                                    <img src="{{ $thumbnail->temporaryUrl() }}" class="mt-2 h-20 rounded">
                                @endif
                            </div>
                            <div>
                                <label class="block text-sm font-medium mb-1" style="color: var(--text-secondary);">Product File</label>
                                <input type="file" wire:model="productFile" class="input w-full text-sm">
                                <div wire:loading wire:target="productFile" class="text-xs text-primary-500 mt-1">Uploading...</div>
                                @error('productFile')<span class="text-red-500 text-xs">{{ $message }}</span>@enderror
                                @if($existingFileName)<p class="text-xs mt-1" style="color: var(--text-muted);">Current: {{ $existingFileName }}</p>@endif
                            </div>
                        </div>

                        <div class="grid grid-cols-3 gap-4">
                            <div>
                                <label class="block text-sm font-medium mb-1" style="color: var(--text-secondary);">Type *</label>
                                <select wire:model.live="type" class="input w-full">
                                    <option value="paid">Paid</option>
                                    <option value="free">Free</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium mb-1" style="color: var(--text-secondary);">Price (Rp)</label>
                                <input type="number" wire:model="price" class="input w-full" min="0" {{ $type === 'free' ? 'disabled' : '' }}>
                                @error('price')<span class="text-red-500 text-xs">{{ $message }}</span>@enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium mb-1" style="color: var(--text-secondary);">Sale Price</label>
                                <input type="number" wire:model="sale_price" class="input w-full" min="0" placeholder="Optional" {{ $type === 'free' ? 'disabled' : '' }}>
                                @error('sale_price')<span class="text-red-500 text-xs">{{ $message }}</span>@enderror
                            </div>
                        </div>

                        <div class="grid grid-cols-3 gap-4">
                            <div>
                                <label class="block text-sm font-medium mb-1" style="color: var(--text-secondary);">Version</label>
                                <input type="text" wire:model="version" class="input w-full">
                                @error('version')<span class="text-red-500 text-xs">{{ $message }}</span>@enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium mb-1" style="color: var(--text-secondary);">Demo URL</label>
                                <input type="text" wire:model="demo_url" class="input w-full" placeholder="https://...">
                                @error('demo_url')<span class="text-red-500 text-xs">{{ $message }}</span>@enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium mb-1" style="color: var(--text-secondary);">Docs URL</label>
                                <input type="text" wire:model="documentation_url" class="input w-full" placeholder="https://...">
                                @error('documentation_url')<span class="text-red-500 text-xs">{{ $message }}</span>@enderror
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium mb-1" style="color: var(--text-secondary);">Features (one per line)</label>
                                <textarea wire:model="features" class="input w-full" rows="3"></textarea>
                            </div>
                            <div>
                                <label class="block text-sm font-medium mb-1" style="color: var(--text-secondary);">Requirements (one per line)</label>
                                <textarea wire:model="requirements" class="input w-full" rows="3"></textarea>
                            </div>
                        </div>

                        <div class="flex flex-wrap items-center gap-6">
                            <label class="flex items-center gap-2"><input type="checkbox" wire:model.live="requires_license" class="rounded"><span class="text-sm" style="color: var(--text-secondary);">Requires License</span></label>
                            @if($requires_license)
                                <div class="flex items-center gap-2">
                                    <span class="text-sm" style="color: var(--text-secondary);">Duration:</span>
                                    <input type="number" wire:model="license_duration_days" class="input w-24" min="1" placeholder="days">
                                </div>
                            @endif
                            <label class="flex items-center gap-2"><input type="checkbox" wire:model="is_active" class="rounded"><span class="text-sm" style="color: var(--text-secondary);">Active</span></label>
                            <label class="flex items-center gap-2"><input type="checkbox" wire:model="is_featured" class="rounded"><span class="text-sm" style="color: var(--text-secondary);">Featured</span></label>
                        </div>

                        <div class="flex justify-end gap-3 pt-4">
                            <button type="button" wire:click="closeModal" class="btn-secondary">Cancel</button>
                            <button type="submit" class="btn-primary" wire:loading.attr="disabled" wire:loading.class="opacity-50">
                                <span wire:loading.remove wire:target="save">{{ $isEditing ? 'Update' : 'Create' }}</span>
                                <span wire:loading wire:target="save">Saving...</span>
                            </button>
                        </div>

                        @if($errors->any())
                            <div class="p-3 rounded-lg bg-red-500/10 border border-red-500/20">
                                <p class="text-red-500 text-sm font-medium mb-1">Please fix the following errors:</p>
                                <ul class="text-red-400 text-xs list-disc list-inside">
                                    @foreach($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                    </form>
                </div>
            </div>
        </div>
    @endif
</div>
