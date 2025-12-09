<div>
    <!-- Header -->
    <div class="mb-8">
        <h1 class="text-2xl font-bold" style="color: var(--text-primary);">Settings</h1>
        <p class="mt-2" style="color: var(--text-secondary);">Manage your account settings</p>
    </div>

    <!-- API Key Section -->
    <div class="card">
        <div class="flex items-center gap-3 mb-6">
            <div class="w-10 h-10 rounded-xl bg-primary-500/20 flex items-center justify-center">
                <svg class="w-5 h-5 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                </svg>
            </div>
            <div>
                <h2 class="text-lg font-semibold" style="color: var(--text-primary);">Personal API Key</h2>
                <p class="text-sm" style="color: var(--text-secondary);">Use your own Gemini API key for metadata generation</p>
            </div>
        </div>

        @if(!$isSubscribed)
            <div class="p-4 rounded-xl bg-amber-500/10 border border-amber-500/20 mb-6">
                <div class="flex items-start gap-3">
                    <svg class="w-5 h-5 text-amber-500 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                    <div>
                        <p class="font-medium text-amber-500">Subscription Required</p>
                        <p class="text-sm mt-1" style="color: var(--text-secondary);">
                            You need an active subscription to use your own API key.
                            <a href="{{ route('subscription') }}" class="text-primary-500 hover:text-primary-400 font-medium" wire:navigate>
                                Upgrade now →
                            </a>
                        </p>
                    </div>
                </div>
            </div>
        @else
            @if($message)
                <div class="mb-6 p-4 bg-emerald-500/20 border border-emerald-500/30 rounded-xl text-emerald-400">
                    {{ $message }}
                </div>
            @endif

            @if($error)
                <div class="mb-6 p-4 bg-red-500/20 border border-red-500/30 rounded-xl text-red-400">
                    {{ $error }}
                </div>
            @endif

            <form wire:submit="saveApiKey" class="space-y-6">
                <div>
                    <label class="label">Gemini API Key</label>
                    <div class="relative">
                        <input 
                            type="{{ $showApiKey ? 'text' : 'password' }}" 
                            wire:model="geminiApiKey" 
                            class="input pr-24" 
                            placeholder="AIza..."
                        >
                        <button 
                            type="button"
                            wire:click="toggleShowApiKey"
                            class="absolute right-3 top-1/2 -translate-y-1/2 text-sm font-medium"
                            style="color: var(--text-secondary);"
                        >
                            {{ $showApiKey ? 'Hide' : 'Show' }}
                        </button>
                    </div>
                    <p class="mt-2 text-sm" style="color: var(--text-muted);">
                        Get your API key from 
                        <a href="https://aistudio.google.com/app/apikey" target="_blank" class="text-primary-500 hover:text-primary-400">
                            Google AI Studio →
                        </a>
                    </p>
                </div>

                <div class="flex items-center gap-4">
                    <button type="submit" class="btn-primary">
                        <span wire:loading.remove wire:target="saveApiKey">Save API Key</span>
                        <span wire:loading wire:target="saveApiKey">Validating...</span>
                    </button>

                    @if($hasApiKey)
                        <span class="flex items-center gap-2 text-sm text-emerald-500">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            Using personal API key
                        </span>
                    @endif
                </div>
            </form>

            <div class="mt-6 pt-6" style="border-top: 1px solid var(--border-color);">
                <h3 class="text-sm font-medium mb-3" style="color: var(--text-primary);">Benefits of using your own API key:</h3>
                <ul class="space-y-2 text-sm" style="color: var(--text-secondary);">
                    <li class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        No shared quota limits
                    </li>
                    <li class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        Higher rate limits with your own quota
                    </li>
                    <li class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        Better reliability during peak usage
                    </li>
                </ul>
            </div>
        @endif
    </div>
</div>
