<div
    x-data="{
        copied: false,
        showToast(message) {
            this.copied = true;
            setTimeout(() => this.copied = false, 2000);
        }
    }"
    x-on:copy-to-clipboard.window="
        navigator.clipboard.writeText($event.detail[0].text);
        showToast('Keywords copied!');
    "
>
    <!-- Toast Notification -->
    <div 
        x-show="copied" 
        x-transition
        class="fixed bottom-4 right-4 px-4 py-2 bg-emerald-500 text-white rounded-lg shadow-lg z-50"
    >
        ✓ Keywords copied!
    </div>

    <!-- Header -->
    <div class="mb-8">
        <h1 class="text-2xl font-bold" style="color: var(--text-primary);">Keyword Generator</h1>
        <p class="mt-2" style="color: var(--text-secondary);">Generate SEO-optimized keywords from your title - Free & Unlimited!</p>
    </div>

    <!-- Main Card -->
    <div class="card">
        <!-- Error Message -->
        @if($error)
            <div class="mb-6 p-4 bg-red-500/20 border border-red-500/30 rounded-xl text-red-400">
                {{ $error }}
            </div>
        @endif

        <!-- Input Form -->
        <form wire:submit="generateKeywords" class="space-y-6">
            <!-- Title Input -->
            <div>
                <label class="label">Title / Description</label>
                <textarea 
                    wire:model="title" 
                    class="input min-h-[100px]" 
                    placeholder="Enter a descriptive title for your image, e.g., 'Young professional woman working on laptop in modern coffee shop'"
                    @if($isGenerating) disabled @endif
                ></textarea>
                @error('title')
                    <p class="mt-2 text-sm text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <!-- Keyword Count -->
            <div>
                <label class="label">Number of Keywords</label>
                <div class="flex items-center gap-4">
                    <input 
                        type="range" 
                        wire:model.live="keywordCount" 
                        min="10" 
                        max="50" 
                        step="5"
                        class="flex-1 h-2 rounded-lg appearance-none cursor-pointer"
                        style="background: var(--bg-hover);"
                        @if($isGenerating) disabled @endif
                    >
                    <span class="text-lg font-medium px-3 py-1 rounded-lg" style="background-color: var(--bg-hover); color: var(--text-primary);">
                        {{ $keywordCount }}
                    </span>
                </div>
            </div>

            <!-- Generate Button -->
            <button 
                type="submit" 
                class="btn-primary w-full"
                wire:loading.attr="disabled"
                @if($isGenerating) disabled @endif
            >
                <span wire:loading.remove wire:target="generateKeywords">
                    <svg class="w-5 h-5 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                    Generate Keywords
                </span>
                <span wire:loading wire:target="generateKeywords" class="flex items-center justify-center gap-2">
                    <span class="spinner"></span>
                    Generating...
                </span>
            </button>
        </form>

        <!-- Generated Keywords Result -->
        @if($generatedKeywords)
            <div class="mt-8 pt-8" style="border-top: 1px solid var(--border-color);">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold" style="color: var(--text-primary);">Generated Keywords</h3>
                    <button 
                        wire:click="copyKeywords"
                        class="flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-medium transition-colors"
                        style="background-color: var(--bg-hover); color: var(--text-primary);"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                        </svg>
                        Copy All
                    </button>
                </div>
                
                <div class="p-4 rounded-xl" style="background-color: var(--bg-hover);">
                    <p style="color: var(--text-secondary); line-height: 1.8;">
                        {{ $generatedKeywords }}
                    </p>
                </div>

                <!-- Keyword Tags Preview -->
                <div class="mt-4 flex flex-wrap gap-2">
                    @foreach(explode(',', $generatedKeywords) as $keyword)
                        <span class="px-3 py-1 rounded-full text-sm" style="background-color: var(--primary-500); background-opacity: 0.2; color: var(--primary-500); background-color: rgba(var(--primary-500-rgb, 99, 102, 241), 0.2);">
                            {{ trim($keyword) }}
                        </span>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</div>
