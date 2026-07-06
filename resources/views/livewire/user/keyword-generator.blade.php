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
    <div 
        x-show="copied" 
        x-transition
        class="fixed bottom-4 right-4 px-4 py-2 rounded-lg text-sm text-white z-50"
        style="background-color: var(--success);"
    >
        Keywords copied!
    </div>

    <div class="section-header">
        <h1>Keyword Generator</h1>
        <p>Generate SEO-optimized keywords from your title - Free & Unlimited!</p>
    </div>

    <div class="card">
        @if($error)
            <div class="mb-5 p-3 rounded-lg text-sm" style="background-color: var(--danger-muted); color: var(--danger);">
                {{ $error }}
            </div>
        @endif

        <form wire:submit="generateKeywords" class="space-y-5">
            <div>
                <label class="label">Title / Description</label>
                <textarea 
                    wire:model="title" 
                    class="input min-h-[100px]" 
                    placeholder="Enter a descriptive title for your image..."
                    @if($isGenerating) disabled @endif
                ></textarea>
                @error('title')
                    <p class="mt-1.5 text-xs" style="color: var(--danger);">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="label">Number of Keywords</label>
                <div class="flex items-center gap-4">
                    <input 
                        type="range" 
                        wire:model.live="keywordCount" 
                        min="10" 
                        max="50" 
                        step="5"
                        class="flex-1 h-1.5 rounded-full appearance-none cursor-pointer"
                        style="background: var(--bg-muted);"
                        @if($isGenerating) disabled @endif
                    >
                    <span class="text-sm font-medium px-2.5 py-1 rounded-md" style="background-color: var(--bg-muted); color: var(--text-primary);">
                        {{ $keywordCount }}
                    </span>
                </div>
            </div>

            <button 
                type="submit" 
                class="btn-primary w-full"
                wire:loading.attr="disabled"
                @if($isGenerating) disabled @endif
            >
                <span wire:loading.remove wire:target="generateKeywords">Generate Keywords</span>
                <span wire:loading wire:target="generateKeywords" class="flex items-center justify-center gap-2">
                    <span class="spinner"></span>
                    Generating...
                </span>
            </button>
        </form>

        @if($generatedKeywords)
            <div class="mt-6 pt-6" style="border-top: 1px solid var(--border-color);">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-sm font-semibold" style="color: var(--text-primary);">Generated Keywords</h3>
                    <button 
                        wire:click="copyKeywords"
                        class="btn-ghost"
                    >
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                        </svg>
                        Copy All
                    </button>
                </div>
                
                <div class="p-3 rounded-lg text-sm leading-relaxed" style="background-color: var(--bg-muted); color: var(--text-secondary);">
                    {{ $generatedKeywords }}
                </div>

                <div class="mt-3 flex flex-wrap gap-1.5">
                    @foreach(explode(',', $generatedKeywords) as $keyword)
                        <span class="tag">{{ trim($keyword) }}</span>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</div>
