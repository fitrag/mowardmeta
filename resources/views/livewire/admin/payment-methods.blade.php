<div class="space-y-8">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl lg:text-3xl font-bold" style="color: var(--text-primary);">Payment Methods</h1>
            <p class="mt-1" style="color: var(--text-secondary);">Manage bank accounts and payment options</p>
        </div>

        <button wire:click="openCreateModal" class="btn-primary">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Add Payment Method
        </button>
    </div>

    <!-- Table -->
    <div class="card overflow-hidden p-0">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead style="background-color: var(--bg-hover); border-bottom: 1px solid var(--border-color);">
                    <tr>
                        <th class="px-4 py-3 text-left text-sm font-medium" style="color: var(--text-secondary);">Bank / Method</th>
                        <th class="px-4 py-3 text-left text-sm font-medium" style="color: var(--text-secondary);">Account Number</th>
                        <th class="px-4 py-3 text-left text-sm font-medium hidden md:table-cell" style="color: var(--text-secondary);">Account Holder</th>
                        <th class="px-4 py-3 text-left text-sm font-medium hidden md:table-cell" style="color: var(--text-secondary);">Status</th>
                        <th class="px-4 py-3 text-right text-sm font-medium" style="color: var(--text-secondary);">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($methods as $method)
                        <tr class="transition-colors" style="border-bottom: 1px solid var(--border-color);" onmouseover="this.style.backgroundColor='var(--bg-hover)'" onmouseout="this.style.backgroundColor='transparent'">
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-xl flex items-center justify-center font-bold text-sm" style="background-color: var(--bg-hover); color: var(--text-primary);">
                                        {{ strtoupper(substr($method->name, 0, 2)) }}
                                    </div>
                                    <div>
                                        <p class="font-medium" style="color: var(--text-primary);">{{ $method->name }}</p>
                                        @if($method->description)
                                            <p class="text-sm" style="color: var(--text-secondary);">{{ Str::limit($method->description, 30) }}</p>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                <span class="font-mono text-sm" style="color: var(--text-primary);">{{ $method->account_number }}</span>
                            </td>
                            <td class="px-4 py-3 hidden md:table-cell">
                                <span class="text-sm" style="color: var(--text-primary);">{{ $method->account_holder_name }}</span>
                            </td>
                            <td class="px-4 py-3 hidden md:table-cell">
                                <button 
                                    wire:click="toggleActive({{ $method->id }})"
                                    class="flex items-center gap-2"
                                >
                                    @if($method->is_active)
                                        <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                                        <span class="text-sm text-emerald-600">Active</span>
                                    @else
                                        <span class="w-2 h-2 rounded-full bg-red-500"></span>
                                        <span class="text-sm text-red-600">Inactive</span>
                                    @endif
                                </button>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <button 
                                        wire:click="openEditModal({{ $method->id }})"
                                        class="p-2 rounded-lg transition-colors"
                                        style="color: var(--text-secondary);"
                                        onmouseover="this.style.backgroundColor='var(--bg-hover)'"
                                        onmouseout="this.style.backgroundColor='transparent'"
                                    >
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                    </button>
                                    <button 
                                        wire:click="delete({{ $method->id }})"
                                        wire:confirm="Are you sure you want to delete this payment method?"
                                        class="p-2 hover:bg-red-500/20 rounded-lg text-red-500 transition-colors"
                                    >
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-12 text-center">
                                <div class="flex flex-col items-center">
                                    <svg class="w-12 h-12 mb-4" style="color: var(--text-muted);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                                    </svg>
                                    <p class="font-medium" style="color: var(--text-primary);">No payment methods yet</p>
                                    <p class="text-sm mt-1" style="color: var(--text-secondary);">Add your first payment method to get started</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal -->
    @if($showModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" wire:click="closeModal"></div>
            
            <div class="relative rounded-2xl w-full max-w-md p-6 animate-fade-in" style="background-color: var(--bg-secondary); border: 1px solid var(--border-color);">
                <h2 class="text-xl font-bold mb-6" style="color: var(--text-primary);">
                    {{ $isEditing ? 'Edit Payment Method' : 'Add Payment Method' }}
                </h2>

                <form wire:submit="save" class="space-y-4">
                    <div>
                        <label for="name" class="label">Bank / Method Name</label>
                        <input type="text" id="name" wire:model="name" class="input" placeholder="e.g. BRI, BCA, Dana">
                        @error('name') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="account_number" class="label">Account Number</label>
                        <input type="text" id="account_number" wire:model="account_number" class="input" placeholder="1234567890">
                        @error('account_number') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="account_holder_name" class="label">Account Holder Name</label>
                        <input type="text" id="account_holder_name" wire:model="account_holder_name" class="input" placeholder="John Doe">
                        @error('account_holder_name') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="description" class="label">Description (optional)</label>
                        <textarea id="description" wire:model="description" class="input" rows="2" placeholder="Additional instructions..."></textarea>
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
