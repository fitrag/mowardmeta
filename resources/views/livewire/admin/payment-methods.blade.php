<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div class="section-header mb-0">
            <h1>Payment Methods</h1>
            <p>Manage bank accounts and payment options</p>
        </div>

        <button wire:click="openCreateModal" class="btn-primary">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Add Payment Method
        </button>
    </div>

    <div class="card overflow-hidden p-0">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="table-header">
                    <tr>
                        <th class="px-4 py-2.5 text-left text-xs font-medium" style="color: var(--text-muted);">Bank / Method</th>
                        <th class="px-4 py-2.5 text-left text-xs font-medium" style="color: var(--text-muted);">Account Number</th>
                        <th class="px-4 py-2.5 text-left text-xs font-medium hidden md:table-cell" style="color: var(--text-muted);">Account Holder</th>
                        <th class="px-4 py-2.5 text-left text-xs font-medium hidden md:table-cell" style="color: var(--text-muted);">Status</th>
                        <th class="px-4 py-2.5 text-right text-xs font-medium" style="color: var(--text-muted);">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($methods as $method)
                        <tr class="table-row">
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-3">
                                    <div class="icon-box-sm flex-shrink-0" style="background-color: var(--bg-muted);">
                                        <span class="text-xs font-bold" style="color: var(--text-secondary);">{{ strtoupper(substr($method->name, 0, 2)) }}</span>
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium" style="color: var(--text-primary);">{{ $method->name }}</p>
                                        @if($method->description)
                                            <p class="text-xs truncate" style="color: var(--text-muted);">{{ Str::limit($method->description, 30) }}</p>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                <span class="font-mono text-sm" style="color: var(--text-primary);">{{ $method->account_number }}</span>
                            </td>
                            <td class="px-4 py-3 hidden md:table-cell">
                                <span class="text-sm" style="color: var(--text-secondary);">{{ $method->account_holder_name }}</span>
                            </td>
                            <td class="px-4 py-3 hidden md:table-cell">
                                <button wire:click="toggleActive({{ $method->id }})" class="flex items-center gap-1.5">
                                    <span class="w-1.5 h-1.5 rounded-full {{ $method->is_active ? 'bg-emerald-500' : 'bg-red-500' }}"></span>
                                    <span class="text-xs {{ $method->is_active ? 'text-emerald-500' : 'text-red-500' }}">{{ $method->is_active ? 'Active' : 'Inactive' }}</span>
                                </button>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <div class="flex items-center justify-end gap-1">
                                    <button wire:click="openEditModal({{ $method->id }})" class="p-1.5 rounded-md transition-colors btn-ghost">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                    </button>
                                    <button wire:click="delete({{ $method->id }})" wire:confirm="Are you sure?" class="p-1.5 rounded-md transition-colors text-red-500 hover:bg-red-500/10">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-10 text-center">
                                <div class="empty-state">
                                    <div class="empty-state-icon">
                                        <svg class="w-6 h-6" style="color: var(--text-muted);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                                        </svg>
                                    </div>
                                    <h3>No payment methods</h3>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($showModal)
        <div class="modal-overlay">
            <div class="modal-backdrop" wire:click="closeModal"></div>
            <div class="modal-content">
                <h2 class="text-base font-semibold mb-5" style="color: var(--text-primary);">
                    {{ $isEditing ? 'Edit Payment Method' : 'Add Payment Method' }}
                </h2>
                <form wire:submit="save" class="space-y-4">
                    <div>
                        <label for="name" class="label">Bank / Method Name</label>
                        <input type="text" id="name" wire:model="name" class="input" placeholder="e.g. BRI, BCA, Dana">
                        @error('name') <p class="mt-1 text-xs" style="color: var(--danger);">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="account_number" class="label">Account Number</label>
                        <input type="text" id="account_number" wire:model="account_number" class="input" placeholder="1234567890">
                        @error('account_number') <p class="mt-1 text-xs" style="color: var(--danger);">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="account_holder_name" class="label">Account Holder Name</label>
                        <input type="text" id="account_holder_name" wire:model="account_holder_name" class="input" placeholder="John Doe">
                        @error('account_holder_name') <p class="mt-1 text-xs" style="color: var(--danger);">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="description" class="label">Description (optional)</label>
                        <textarea id="description" wire:model="description" class="input" rows="2" placeholder="Additional instructions..."></textarea>
                        @error('description') <p class="mt-1 text-xs" style="color: var(--danger);">{{ $message }}</p> @enderror
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label for="sort_order" class="label">Sort Order</label>
                            <input type="number" id="sort_order" wire:model="sort_order" class="input" min="0">
                        </div>
                        <div class="flex items-end pb-1">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" wire:model="is_active" class="w-4 h-4 rounded transition-colors" style="border-color: var(--border-color); background-color: var(--bg-input);">
                                <span class="text-sm" style="color: var(--text-primary);">Active</span>
                            </label>
                        </div>
                    </div>
                    <div class="flex gap-3 pt-3">
                        <button type="button" wire:click="closeModal" class="btn-secondary flex-1">Cancel</button>
                        <button type="submit" class="btn-primary flex-1">{{ $isEditing ? 'Update' : 'Create' }}</button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
