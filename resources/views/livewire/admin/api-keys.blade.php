<div class="space-y-8">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl lg:text-3xl font-bold" style="color: var(--text-primary);">API Keys</h1>
            <p class="mt-1" style="color: var(--text-secondary);">Manage Gemini API keys for metadata generation</p>
        </div>

        <button wire:click="openCreateModal" class="btn-primary">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Add API Key
        </button>
    </div>

    <!-- Info Card -->
    <div class="card bg-primary-500/10" style="border-color: rgba(99, 102, 241, 0.3);">
        <div class="flex items-start gap-4">
            <div class="w-10 h-10 rounded-xl bg-primary-500/20 flex items-center justify-center flex-shrink-0">
                <svg class="w-5 h-5 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <div>
                <h3 class="font-medium text-primary-600">How it works</h3>
                <p class="text-sm mt-1" style="color: var(--text-secondary);">
                    You can add multiple API keys. The system will randomly select an active key for each generation request. 
                    This helps distribute usage across multiple keys and avoid rate limits.
                </p>
            </div>
        </div>
    </div>

    <!-- Table -->
    <div class="card overflow-hidden p-0">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead style="background-color: var(--bg-hover); border-bottom: 1px solid var(--border-color);">
                    <tr>
                        <th class="px-4 py-3 text-left text-sm font-medium" style="color: var(--text-secondary);">Name</th>
                        <th class="px-4 py-3 text-left text-sm font-medium" style="color: var(--text-secondary);">API Key</th>
                        <th class="px-4 py-3 text-left text-sm font-medium hidden md:table-cell" style="color: var(--text-secondary);">Usage</th>
                        <th class="px-4 py-3 text-left text-sm font-medium" style="color: var(--text-secondary);">Status</th>
                        <th class="px-4 py-3 text-right text-sm font-medium" style="color: var(--text-secondary);">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($apiKeys as $apiKey)
                        <tr class="transition-colors" style="border-bottom: 1px solid var(--border-color);" onmouseover="this.style.backgroundColor='var(--bg-hover)'" onmouseout="this.style.backgroundColor='transparent'">
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-xl bg-accent-amber/20 flex items-center justify-center">
                                        <svg class="w-5 h-5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="font-medium" style="color: var(--text-primary);">{{ $apiKey->name }}</p>
                                        <p class="text-xs" style="color: var(--text-secondary);">{{ ucfirst($apiKey->provider) }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                <code class="px-2 py-1 rounded text-sm" style="background-color: var(--bg-hover); color: var(--text-secondary);">
                                    {{ substr($apiKey->api_key, 0, 8) }}...{{ substr($apiKey->api_key, -4) }}
                                </code>
                            </td>
                            <td class="px-4 py-3 hidden md:table-cell">
                                <div>
                                    <p class="text-sm" style="color: var(--text-primary);">{{ number_format($apiKey->usage_count) }} requests</p>
                                    @if($apiKey->last_used_at)
                                        <p class="text-xs" style="color: var(--text-secondary);">Last: {{ $apiKey->last_used_at->diffForHumans() }}</p>
                                    @else
                                        <p class="text-xs" style="color: var(--text-secondary);">Never used</p>
                                    @endif
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                <button 
                                    wire:click="toggleActive({{ $apiKey->id }})"
                                    class="flex items-center gap-2"
                                >
                                    @if($apiKey->is_active)
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
                                        wire:click="openEditModal({{ $apiKey->id }})"
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
                                        wire:click="delete({{ $apiKey->id }})"
                                        wire:confirm="Are you sure you want to delete this API key?"
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
                                    <div class="w-16 h-16 rounded-full flex items-center justify-center mb-4" style="background-color: var(--bg-hover);">
                                        <svg class="w-8 h-8" style="color: var(--text-secondary);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                                        </svg>
                                    </div>
                                    <h3 class="font-medium mb-1" style="color: var(--text-primary);">No API keys configured</h3>
                                    <p class="text-sm mb-4" style="color: var(--text-secondary);">Add a Gemini API key to enable metadata generation</p>
                                    <button wire:click="openCreateModal" class="btn-primary">
                                        Add API Key
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($apiKeys->hasPages())
            <div class="px-4 py-3" style="border-top: 1px solid var(--border-color);">
                {{ $apiKeys->links() }}
            </div>
        @endif
    </div>

    <!-- Modal -->
    @if($showModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" wire:click="closeModal"></div>
            
            <div class="relative rounded-2xl w-full max-w-md p-6 animate-fade-in" style="background-color: var(--bg-secondary); border: 1px solid var(--border-color);">
                <h2 class="text-xl font-bold mb-6" style="color: var(--text-primary);">
                    {{ $isEditing ? 'Edit API Key' : 'Add New API Key' }}
                </h2>

                <form wire:submit="save" class="space-y-4">
                    <div>
                        <label for="name" class="label">Name</label>
                        <input type="text" id="name" wire:model="name" class="input" placeholder="My API Key">
                        @error('name') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="api_key" class="label">API Key</label>
                        <input type="text" id="api_key" wire:model="api_key" class="input font-mono text-sm" placeholder="AIzaSy...">
                        @error('api_key') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="provider" class="label">Provider</label>
                        <select id="provider" wire:model="provider" class="input">
                            <option value="gemini">Google Gemini</option>
                        </select>
                        @error('provider') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
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
