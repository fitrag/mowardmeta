<div>
    <div class="section-header">
        <h1 class="text-sm font-semibold" style="color: var(--text-primary);">Settings</h1>
        <p class="text-xs" style="color: var(--text-muted);">Manage your account settings</p>
    </div>

    <div class="card">
        <div class="flex items-center gap-3 mb-4">
            <div class="icon-box-sm">
                <svg class="w-4 h-4" style="color: var(--accent);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                </svg>
            </div>
            <div>
                <h2 class="text-sm font-medium" style="color: var(--text-primary);">Personal API Key</h2>
                <p class="text-xs" style="color: var(--text-muted);">Use your own Gemini API key for metadata generation</p>
            </div>
        </div>

        @if(!$isSubscribed)
            <div class="p-3 rounded-lg mb-4" style="background-color: var(--warning-muted); border: 1px solid var(--warning);">
                <div class="flex items-start gap-2">
                    <svg class="w-4 h-4 mt-0.5" style="color: var(--warning);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                    <div>
                        <p class="text-xs font-medium" style="color: var(--warning);">Subscription Required</p>
                        <p class="text-xs mt-1" style="color: var(--text-secondary);">
                            You need an active subscription to use your own API key.
                            <a href="{{ route('subscription') }}" class="font-medium" style="color: var(--accent);" wire:navigate>
                                Upgrade now &rarr;
                            </a>
                        </p>
                    </div>
                </div>
            </div>
        @else
            @if($message)
                <div class="mb-4 p-3 rounded-lg text-xs" style="background-color: var(--success-muted); border: 1px solid var(--success); color: var(--success);">
                    {{ $message }}
                </div>
            @endif

            @if($error)
                <div class="mb-4 p-3 rounded-lg text-xs" style="background-color: var(--danger-muted); border: 1px solid var(--danger); color: var(--danger);">
                    {{ $error }}
                </div>
            @endif

            <form wire:submit="saveApiKey" class="space-y-4">
                <div>
                    <label class="label">Gemini API Key</label>
                    <div class="relative">
                        <input
                            type="{{ $showApiKey ? 'text' : 'password' }}"
                            wire:model="geminiApiKey"
                            class="input pr-20 text-sm"
                            placeholder="AIza..."
                        >
                        <button
                            type="button"
                            wire:click="toggleShowApiKey"
                            class="absolute right-3 top-1/2 -translate-y-1/2 text-xs font-medium"
                            style="color: var(--text-muted);"
                        >
                            {{ $showApiKey ? 'Hide' : 'Show' }}
                        </button>
                    </div>
                    <p class="mt-2 text-xs" style="color: var(--text-muted);">
                        Get your API key from
                        <a href="https://aistudio.google.com/app/apikey" target="_blank" style="color: var(--accent);">
                            Google AI Studio &rarr;
                        </a>
                    </p>
                </div>

                <div class="flex items-center gap-4">
                    <button type="submit" class="btn-primary text-sm">
                        <span wire:loading.remove wire:target="saveApiKey">Save API Key</span>
                        <span wire:loading wire:target="saveApiKey">Validating...</span>
                    </button>

                    @if($hasApiKey)
                        <span class="flex items-center gap-1.5 text-xs" style="color: var(--success);">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            Using personal API key
                        </span>
                    @endif
                </div>
            </form>

            <div class="mt-4 pt-4" style="border-top: 1px solid var(--border-color);">
                <h3 class="text-xs font-medium mb-2" style="color: var(--text-primary);">Benefits of using your own API key:</h3>
                <ul class="space-y-1.5 text-xs" style="color: var(--text-secondary);">
                    <li class="flex items-center gap-2">
                        <svg class="w-3.5 h-3.5" style="color: var(--success);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        No shared quota limits
                    </li>
                    <li class="flex items-center gap-2">
                        <svg class="w-3.5 h-3.5" style="color: var(--success);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        Higher rate limits with your own quota
                    </li>
                    <li class="flex items-center gap-2">
                        <svg class="w-3.5 h-3.5" style="color: var(--success);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        Better reliability during peak usage
                    </li>
                </ul>
            </div>
        @endif
    </div>
</div>
