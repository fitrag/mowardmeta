<div 
    class="space-y-6" 
    x-data="metadataImageUploader()"
    x-init="initDB()"
    x-on:copy-to-clipboard.window="
        navigator.clipboard.writeText($event.detail[0].text);
        showToast(($event.detail[0].type === 'title' ? 'Title' : 'Keywords') + ' copied!');
    "
    x-on:request-image-data.window="sendImageToServer($event.detail[0].index)"
    x-on:clear-images.window="clearAllImages()"
    x-on:save-history-item.window="saveToHistory($event.detail)"
>
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div class="section-header mb-0">
            <h1>Generate Metadata</h1>
            <p>AI-powered metadata for stock photography</p>
        </div>
        
        @if(!$isSubscribed)
            <div class="flex items-center gap-2">
                <div class="flex items-center gap-2 px-3 py-1.5 rounded-lg text-xs font-medium" style="background-color: var(--bg-muted); border: 1px solid var(--border-color);">
                    <span class="w-1.5 h-1.5 rounded-full {{ $canGenerate ? 'bg-emerald-500' : 'bg-red-500' }}"></span>
                    @if($canGenerate)
                        <span style="color: var(--text-secondary);">{{ $remainingGenerations }}/{{ $dailyLimit }} remaining</span>
                    @else
                        <span style="color: var(--danger);">Daily limit reached</span>
                    @endif
                </div>
                <a href="{{ route('subscription') }}" class="btn-primary text-xs px-3 py-1.5" wire:navigate>
                    Upgrade
                </a>
            </div>
        @endif
    </div>

    <!-- Two Column Layout -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
        <!-- Left Column: Upload -->
        <div class="space-y-4">
            <!-- Upload Card -->
            <div class="card">
                <h2 class="text-sm font-semibold mb-3" style="color: var(--text-primary);">Upload Images</h2>
                
                <!-- Upload Area -->
                <div 
                    x-show="localImages.length === 0"
                    x-on:dragover.prevent="isDragging = true"
                    x-on:dragleave.prevent="isDragging = false"
                    x-on:drop.prevent="isDragging = false; handleFiles($event.dataTransfer.files)"
                    class="relative"
                >
                    <input 
                        type="file" 
                        x-ref="fileInput"
                        accept="image/*" 
                        multiple
                        class="hidden" 
                        id="image-upload"
                        x-on:change="handleFiles($event.target.files)"
                        :disabled="$wire.isProcessing"
                    >
                    
                    <label 
                        for="image-upload"
                        class="flex flex-col items-center justify-center w-full py-10 border-2 border-dashed rounded-xl cursor-pointer transition-all duration-200"
                        :class="{ 
                            'opacity-50 cursor-not-allowed': $wire.isProcessing,
                            'border-[var(--accent)] bg-[var(--accent-muted)]': isDragging,
                            'border-[var(--border-color)] hover:border-[var(--accent)]': !isDragging
                        }"
                    >
                        <div x-show="!isCompressing && !$wire.isProcessing" class="flex flex-col items-center">
                            <div class="w-12 h-12 rounded-xl flex items-center justify-center mb-3" style="background-color: var(--accent-muted);">
                                <svg class="w-6 h-6" style="color: var(--accent);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                            </div>
                            <p class="font-medium text-sm" style="color: var(--text-primary);">Drop images or click to upload</p>
                            <p class="text-xs mt-1" style="color: var(--text-secondary);">JPG, PNG • Multiple files • Stored locally</p>
                        </div>
                        
                        <div x-show="isCompressing" x-cloak class="flex flex-col items-center py-2">
                            <div class="w-8 h-8 mb-2 relative">
                                <div class="absolute inset-0 rounded-full border-2" style="border-color: var(--accent-muted);"></div>
                                <div class="absolute inset-0 rounded-full border-2 border-transparent animate-spin" style="border-top-color: var(--accent);"></div>
                            </div>
                            <p class="font-medium text-sm" style="color: var(--text-primary);">Processing images...</p>
                            <p class="text-xs mt-1" style="color: var(--text-secondary);"><span x-text="compressProgress"></span> / <span x-text="compressTotal"></span></p>
                        </div>
                    </label>
                </div>

                <!-- Uploaded Images Grid -->
                <template x-if="localImages.length > 0">
                    <div class="mt-4">
                        <div class="flex items-center justify-between mb-3">
                            <span class="text-xs font-medium" style="color: var(--text-secondary);">
                                <span x-text="localImages.length"></span> image(s)
                            </span>
                            <button x-show="!$wire.isProcessing" @click="clearAllImages()" class="text-xs flex items-center gap-1 px-2 py-1 rounded-md transition-colors" style="color: var(--danger);" onmouseover="this.style.backgroundColor='var(--danger-muted)'" onmouseout="this.style.backgroundColor='transparent'">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                                Clear all
                            </button>
                        </div>
                        
                        <div class="grid grid-cols-5 gap-2">
                            <template x-for="(img, index) in localImages" :key="img.id">
                                <div class="relative aspect-square group rounded-lg overflow-hidden" style="background-color: var(--bg-muted);">
                                    <img :src="img.thumbnail" alt="" class="w-full h-full object-cover">
                                    
                                    <template x-if="getImageStatus(index) === 'processing'">
                                        <div class="absolute inset-0 flex items-center justify-center" style="background-color: rgba(0,0,0,0.5);">
                                            <div class="w-4 h-4 border-2 rounded-full animate-spin" style="border-color: rgba(255,255,255,0.3); border-top-color: #fff;"></div>
                                        </div>
                                    </template>
                                    <template x-if="getImageStatus(index) === 'completed'">
                                        <div class="absolute inset-0 flex items-center justify-center" style="background-color: rgba(16,185,129,0.7);">
                                            <div class="bg-white rounded-full p-1 shadow-lg">
                                                <svg class="w-3.5 h-3.5" style="color: var(--success);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                                                </svg>
                                            </div>
                                        </div>
                                    </template>
                                    <template x-if="getImageStatus(index) === 'failed'">
                                        <div class="absolute inset-0 flex flex-col items-center justify-center gap-1" style="background-color: rgba(239,68,68,0.5);">
                                            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                            </svg>
                                            <button 
                                                @click="$wire.regenerateImage(index)"
                                                class="px-2 py-0.5 text-[10px] bg-white/90 hover:bg-white rounded font-medium transition-colors"
                                                style="color: var(--danger);"
                                            >
                                                Retry
                                            </button>
                                        </div>
                                    </template>
                                    
                                    <template x-if="!$wire.isProcessing && getImageStatus(index) === 'pending'">
                                        <button 
                                            @click="removeImage(index)"
                                            class="absolute top-1 right-1 w-5 h-5 rounded-full flex items-center justify-center opacity-0 group-hover:opacity-100 transition-all"
                                            style="background-color: rgba(0,0,0,0.6); color: #fff;"
                                            onmouseover="this.style.backgroundColor='var(--danger)'"
                                            onmouseout="this.style.backgroundColor='rgba(0,0,0,0.6)'"
                                        >
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                            </svg>
                                        </button>
                                    </template>
                                </div>
                            </template>
                        </div>
                    </div>
                </template>
            </div>

            <!-- Settings Card -->
            <template x-if="localImages.length > 0">
                <div class="card">
                    <h2 class="text-sm font-semibold mb-3" style="color: var(--text-primary);">Configuration</h2>
                    
                    <div class="space-y-4">
                        <!-- Context Input -->
                        <div>
                            <label class="label">Context (optional)</label>
                            <input 
                                type="text"
                                wire:model="description"
                                class="input text-sm"
                                placeholder="Add context to improve results..."
                                :disabled="$wire.isProcessing"
                            >
                            <p class="text-[11px] mt-1.5" style="color: var(--text-muted);">Provide additional context about the images for better results</p>
                        </div>
                        
                        <template x-if="!$wire.isProcessing">
                            <div class="space-y-4">
                                <!-- Mode Selection -->
                                <div>
                                    <label class="label">Generation Mode</label>
                                    <div class="grid grid-cols-2 gap-2">
                                        <button
                                            wire:click="$set('mode', 'fast')"
                                            class="p-3 rounded-lg text-left transition-all text-sm border"
                                            @if($mode === 'fast')
                                                style="background-color: var(--accent-muted); border-color: var(--accent);"
                                            @else
                                                style="background-color: var(--bg-muted); border-color: var(--border-color);"
                                            @endif
                                        >
                                            <div class="flex items-center gap-2 mb-1">
                                                <div class="w-6 h-6 rounded-md flex items-center justify-center" style="background-color: var(--accent-muted);">
                                                    <svg class="w-3.5 h-3.5" style="color: var(--accent);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                                                    </svg>
                                                </div>
                                                <span class="font-medium" style="color: var(--text-primary);">Fast</span>
                                            </div>
                                            <p class="text-[11px]" style="color: var(--text-muted);">Quick SEO metadata</p>
                                        </button>
                                        <button
                                            wire:click="$set('mode', 'slow')"
                                            class="p-3 rounded-lg text-left transition-all text-sm border"
                                            @if($mode === 'slow')
                                                style="background-color: var(--accent-muted); border-color: var(--accent);"
                                            @else
                                                style="background-color: var(--bg-muted); border-color: var(--border-color);"
                                            @endif
                                        >
                                            <div class="flex items-center gap-2 mb-1">
                                                <div class="w-6 h-6 rounded-md flex items-center justify-center" style="background-color: var(--accent-muted);">
                                                    <svg class="w-3.5 h-3.5" style="color: var(--accent);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                                                    </svg>
                                                </div>
                                                <span class="font-medium" style="color: var(--text-primary);">Detailed</span>
                                            </div>
                                            <p class="text-[11px]" style="color: var(--text-muted);">SEO + descriptive</p>
                                        </button>
                                    </div>
                                </div>

                                <!-- AI Model Selection -->
                                <div>
                                    <label class="label">
                                        AI Model
                                        <span class="font-normal" style="color: var(--text-muted);">({{ ucfirst($currentProvider) }})</span>
                                    </label>
                                    <select 
                                        wire:model.live="selectedModel"
                                        class="input text-sm"
                                    >
                                        @foreach($availableModels as $modelId => $modelName)
                                            <option value="{{ $modelId }}">{{ $modelName }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                
                                <!-- Keyword Count -->
                                <div>
                                    <div class="flex items-center justify-between mb-1.5">
                                        <label class="label mb-0">Keywords Count</label>
                                        <span class="text-xs font-semibold px-2 py-0.5 rounded-md" style="background-color: var(--accent-muted); color: var(--accent);">{{ $keywordCount }}</span>
                                    </div>
                                    <input 
                                        type="range" 
                                        wire:model.live="keywordCount" 
                                        min="10" 
                                        max="50" 
                                        step="5"
                                        class="w-full h-1.5 rounded-full appearance-none cursor-pointer"
                                        style="background-color: var(--bg-muted);"
                                    >
                                    <div class="flex justify-between mt-1">
                                        <span class="text-[11px]" style="color: var(--text-muted);">10</span>
                                        <span class="text-[11px]" style="color: var(--text-muted);">50</span>
                                    </div>
                                </div>
                                
                                <!-- Delay Setting -->
                                <div>
                                    <div class="flex items-center justify-between mb-1.5">
                                        <label class="label mb-0">Delay Between Images</label>
                                        <span class="text-xs font-semibold px-2 py-0.5 rounded-md" style="background-color: var(--accent-muted); color: var(--accent);">{{ $delaySeconds }}s</span>
                                    </div>
                                    <input 
                                        type="range" 
                                        wire:model.live="delaySeconds" 
                                        min="1" 
                                        max="10" 
                                        step="1"
                                        class="w-full h-1.5 rounded-full appearance-none cursor-pointer"
                                        style="background-color: var(--bg-muted);"
                                    >
                                    <p class="text-[11px] mt-1" style="color: var(--text-muted);">Wait time between each image processing</p>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            </template>

            <!-- Progress -->
            @if($isProcessing)
                <div class="card p-4" x-data="{ countdown: 0, isWaiting: false }" x-on:start-countdown.window="countdown = $event.detail.delay / 1000; isWaiting = true; const timer = setInterval(() => { countdown--; if(countdown <= 0) { clearInterval(timer); isWaiting = false; } }, 1000);">
                    <div class="flex items-center justify-between mb-3">
                        <div class="flex items-center gap-2.5">
                            <div class="w-5 h-5 border-2 rounded-full animate-spin" style="border-color: var(--accent-muted); border-top-color: var(--accent);"></div>
                            <span class="text-sm font-medium" style="color: var(--text-primary);">
                                <span x-show="!isWaiting">Processing image {{ $currentIndex + 1 }}...</span>
                                <span x-show="isWaiting" x-cloak>Waiting <span x-text="countdown"></span>s...</span>
                            </span>
                        </div>
                        <span class="text-sm font-bold" style="color: var(--accent);">{{ $processedCount }}/{{ $totalImages }}</span>
                    </div>
                    <div class="w-full h-2 rounded-full overflow-hidden" style="background-color: var(--bg-muted);" x-data="{ progressWidth: {{ $totalImages > 0 ? ($processedCount / $totalImages) * 100 : 0 }} }">
                        <div class="h-full rounded-full transition-all duration-500" x-bind:style="'width: ' + progressWidth + '%; background: linear-gradient(90deg, var(--accent), #06b6d4);'"></div>
                    </div>
                </div>
            @endif

            <!-- Generate Button -->
            <template x-if="localImages.length > 0 && !$wire.isProcessing">
                <button 
                    @click="startGeneration()"
                    class="btn-primary w-full justify-center py-3 text-sm"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                    Generate Metadata
                    <span x-show="localImages.length > 1" class="text-xs opacity-80">(<span x-text="localImages.length"></span> images)</span>
                </button>
            </template>

            @if($error)
                <div class="p-3 rounded-lg flex items-center gap-2" style="background-color: var(--danger-muted); border: 1px solid var(--danger-muted);">
                    <svg class="w-4 h-4 flex-shrink-0" style="color: var(--danger);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <p class="text-xs" style="color: var(--danger);">{{ $error }}</p>
                </div>
            @endif
        </div>

        <!-- Right Column: Results -->
        <div class="card">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h2 class="text-sm font-semibold" style="color: var(--text-primary);">Results</h2>
                    <p class="text-[11px] mt-0.5" style="color: var(--text-muted);">Generated metadata for your images</p>
                </div>
                @if(count($results) > 0 && !$isProcessing)
                    <button 
                        @click="exportToCSV()"
                        class="btn-secondary text-xs"
                    >
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        Export CSV
                    </button>
                @endif
            </div>

            @if(count($results) > 0)
                @if(!$isProcessing && count($results) === $totalImages && $totalImages > 0)
                    <div class="mb-4 p-3 rounded-lg flex items-center gap-2" style="background-color: var(--success-muted);">
                        <svg class="w-4 h-4 flex-shrink-0" style="color: var(--success);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span class="text-xs font-medium" style="color: var(--success);">All {{ $totalImages }} images processed successfully</span>
                    </div>
                @endif
                
                <div class="space-y-3 max-h-[800px] overflow-y-auto pr-1">
                    @foreach($results as $index => $result)
                        <div class="rounded-xl overflow-hidden" style="border: 1px solid var(--border-color); background-color: var(--bg-card);">
                            <div class="flex items-start gap-3 p-3" style="border-bottom: 1px solid var(--border-color); background-color: var(--bg-muted);">
                                <div class="relative w-14 h-14 rounded-lg overflow-hidden flex-shrink-0" style="background-color: var(--bg-secondary);">
                                    <img x-show="getLocalImage({{ $index }})" :src="getLocalImage({{ $index }})?.thumbnail" alt="" class="w-full h-full object-cover">
                                </div>
                                
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-start justify-between gap-2">
                                        <div class="min-w-0">
                                            <h3 class="font-medium text-sm truncate" style="color: var(--text-primary);" title="{{ $result['filename'] }}">
                                                {{ $result['filename'] }}
                                            </h3>
                                            <div class="flex items-center gap-2 mt-1">
                                                <span class="badge badge-success">Ready</span>
                                                <span class="text-[11px]" style="color: var(--text-muted);">
                                                    {{ count(explode(',', $result['keywords'])) }} keywords
                                                </span>
                                            </div>
                                        </div>
                                        
                                        <button 
                                            wire:click="copyToClipboard({{ $index }}, 'title')" 
                                            class="p-1.5 rounded-md transition-colors flex-shrink-0"
                                            style="color: var(--text-muted);"
                                            onmouseover="this.style.backgroundColor='var(--bg-muted)'; this.style.color='var(--text-secondary)'"
                                            onmouseout="this.style.backgroundColor='transparent'; this.style.color='var(--text-muted)'"
                                            title="Copy Title"
                                        >
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3"/>
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="p-3 space-y-3">
                                <div class="space-y-1.5">
                                    <div class="flex items-center justify-between">
                                        <label class="label mb-0">Title</label>
                                        <button 
                                            wire:click="copyToClipboard({{ $index }}, 'title')"
                                            class="text-[11px] font-medium px-2 py-1 rounded-md transition-colors"
                                            style="color: var(--accent);"
                                            onmouseover="this.style.backgroundColor='var(--accent-muted)'"
                                            onmouseout="this.style.backgroundColor='transparent'"
                                        >
                                            Copy
                                        </button>
                                    </div>
                                    <div class="p-2.5 rounded-lg text-sm leading-relaxed" style="background-color: var(--bg-muted); border: 1px solid var(--border-color); color: var(--text-primary);">
                                        {{ $result['title'] }}
                                    </div>
                                </div>
                                
                                <div class="space-y-1.5">
                                    <div class="flex items-center justify-between">
                                        <label class="label mb-0">Keywords</label>
                                        <button 
                                            wire:click="copyToClipboard({{ $index }}, 'keywords')"
                                            class="text-[11px] font-medium px-2 py-1 rounded-md transition-colors"
                                            style="color: var(--accent);"
                                            onmouseover="this.style.backgroundColor='var(--accent-muted)'"
                                            onmouseout="this.style.backgroundColor='transparent'"
                                        >
                                            Copy All
                                        </button>
                                    </div>
                                    <div class="flex flex-wrap gap-1">
                                        @foreach(explode(',', $result['keywords']) as $keyword)
                                            <span class="tag">{{ trim($keyword) }}</span>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="empty-state">
                    <div class="empty-state-icon">
                        <svg class="w-6 h-6" style="color: var(--text-muted);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                        </svg>
                    </div>
                    <h3>No results yet</h3>
                    <p>Upload images and generate metadata to see results here</p>
                </div>
            @endif
        </div>
    </div>

    <!-- Scripts -->
    @script
    <script>
        $wire.on('process-next-delay', (data) => {
            const delay = data[0]?.delay || 3000;
            const nextIndex = data[0]?.nextIndex || 0;
            window.dispatchEvent(new CustomEvent('start-countdown', { detail: { delay: delay } }));
            setTimeout(() => {
                window.dispatchEvent(new CustomEvent('request-image-data', { detail: [{ index: nextIndex }] }));
            }, delay);
        });
        
        $wire.on('save-to-history', (data) => {
            window.dispatchEvent(new CustomEvent('save-history-item', { detail: data[0] }));
        });
    </script>
    @endscript
</div>
