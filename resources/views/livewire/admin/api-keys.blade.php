<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div class="section-header mb-0">
            <h1>API Keys</h1>
            <p>Manage AI API keys for metadata generation</p>
        </div>

        <button wire:click="openCreateModal" class="btn-primary">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Add API Key
        </button>
    </div>

    <div class="card" style="border-color: var(--accent-muted);">
        <div class="flex items-start gap-3">
            <div class="icon-box-sm flex-shrink-0" style="background-color: var(--accent-muted);">
                <svg class="w-4 h-4" style="color: var(--accent);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <div>
                <h3 class="text-sm font-medium" style="color: var(--accent);">How it works</h3>
                <p class="text-xs mt-0.5" style="color: var(--text-secondary);">
                    You can add multiple API keys. The system will randomly select an active key for each generation request. 
                    This helps distribute usage across multiple keys and avoid rate limits.
                </p>
            </div>
        </div>
    </div>

    <div class="card overflow-hidden p-0">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="table-header">
                    <tr>
                        <th class="px-4 py-2.5 text-left text-xs font-medium" style="color: var(--text-muted);">Name</th>
                        <th class="px-4 py-2.5 text-left text-xs font-medium" style="color: var(--text-muted);">API Key</th>
                        <th class="px-4 py-2.5 text-left text-xs font-medium hidden md:table-cell" style="color: var(--text-muted);">Usage</th>
                        <th class="px-4 py-2.5 text-left text-xs font-medium" style="color: var(--text-muted);">Status</th>
                        <th class="px-4 py-2.5 text-right text-xs font-medium" style="color: var(--text-muted);">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($apiKeys as $apiKey)
                        <tr class="table-row">
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-3">
                                    <div class="icon-box-sm flex-shrink-0" style="background-color: var(--warning-muted);">
                                        <svg class="w-4 h-4" style="color: var(--warning);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium" style="color: var(--text-primary);">{{ $apiKey->name }}</p>
                                        <p class="text-xs" style="color: var(--text-muted);">{{ ucfirst($apiKey->provider) }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                <code class="px-2 py-0.5 rounded text-xs" style="background-color: var(--bg-muted); color: var(--text-secondary);">
                                    {{ substr($apiKey->api_key, 0, 8) }}...{{ substr($apiKey->api_key, -4) }}
                                </code>
                            </td>
                            <td class="px-4 py-3 hidden md:table-cell">
                                <div>
                                    <p class="text-sm" style="color: var(--text-primary);">{{ number_format($apiKey->usage_count) }} requests</p>
                                    @if($apiKey->last_used_at)
                                        <p class="text-xs" style="color: var(--text-muted);">Last: {{ $apiKey->last_used_at->diffForHumans() }}</p>
                                    @else
                                        <p class="text-xs" style="color: var(--text-muted);">Never used</p>
                                    @endif
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                <button 
                                    wire:click="toggleActive({{ $apiKey->id }})"
                                    class="flex items-center gap-1.5"
                                >
                                    <span class="w-1.5 h-1.5 rounded-full {{ $apiKey->is_active ? 'bg-emerald-500' : 'bg-red-500' }}"></span>
                                    <span class="text-xs {{ $apiKey->is_active ? 'text-emerald-500' : 'text-red-500' }}">{{ $apiKey->is_active ? 'Active' : 'Inactive' }}</span>
                                </button>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <div class="flex items-center justify-end gap-1">
                                    <button 
                                        wire:click="openEditModal({{ $apiKey->id }})"
                                        class="p-1.5 rounded-md transition-colors btn-ghost"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                    </button>
                                    <button 
                                        wire:click="delete({{ $apiKey->id }})"
                                        wire:confirm="Are you sure you want to delete this API key?"
                                        class="p-1.5 rounded-md transition-colors text-red-500 hover:bg-red-500/10"
                                    >
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
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                                        </svg>
                                    </div>
                                    <h3>No API keys configured</h3>
                                    <p>Add an API key to enable metadata generation</p>
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

    @if($showModal)
        <div class="modal-overlay">
            <div class="modal-backdrop" wire:click="closeModal"></div>
            
            <div class="modal-content">
                <h2 class="text-base font-semibold mb-5" style="color: var(--text-primary);">
                    {{ $isEditing ? 'Edit API Key' : 'Add New API Key' }}
                </h2>

                <form wire:submit="save" class="space-y-4">
                    <div>
                        <label for="name" class="label">Name</label>
                        <input type="text" id="name" wire:model="name" class="input" placeholder="My API Key">
                        @error('name') <p class="mt-1 text-xs" style="color: var(--danger);">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="api_key" class="label">API Key</label>
                        <input type="text" id="api_key" wire:model="api_key" class="input font-mono text-sm" placeholder="AIzaSy...">
                        @error('api_key') <p class="mt-1 text-xs" style="color: var(--danger);">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="provider" class="label">Provider</label>
                        <select id="provider" wire:model="provider" class="input">
                            <option value="gemini">Google Gemini</option>
                            <option value="groq">Groq AI</option>
                            <option value="mistral">Mistral AI</option>
                        </select>
                        @error('provider') <p class="mt-1 text-xs" style="color: var(--danger);">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" wire:model="is_active" class="w-4 h-4 rounded transition-colors" style="border-color: var(--border-color); background-color: var(--bg-input);">
                            <span class="text-sm" style="color: var(--text-primary);">Active</span>
                        </label>
                    </div>

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
