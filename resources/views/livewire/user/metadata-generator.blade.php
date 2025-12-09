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
        <div>
            <h1 class="text-2xl lg:text-3xl font-bold" style="color: var(--text-primary);">Generate Metadata</h1>
            <p class="mt-1" style="color: var(--text-secondary);">AI-powered metadata for stock photography</p>
        </div>
        
        @if(!$isSubscribed)
            <div class="flex items-center gap-3">
                <div class="px-4 py-2 rounded-xl {{ $canGenerate ? 'bg-amber-500/10 border border-amber-500/20' : 'bg-red-500/10 border border-red-500/20' }}">
                    <div class="flex items-center gap-2">
                        @if($canGenerate)
                            <svg class="w-4 h-4 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <span class="text-sm font-medium text-amber-600">{{ $remainingGenerations }}/{{ $dailyLimit }} remaining today</span>
                        @else
                            <svg class="w-4 h-4 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                            </svg>
                            <span class="text-sm font-medium text-red-500">Daily limit reached</span>
                        @endif
                    </div>
                </div>
                <a href="{{ route('subscription') }}" class="px-4 py-2 text-sm font-medium rounded-xl bg-primary-500 text-white hover:bg-primary-400 transition-colors" wire:navigate>
                    Upgrade
                </a>
            </div>
        @endif
    </div>

    <!-- Two Column Layout -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Left Column: Upload -->
        <div class="card">
            <h2 class="text-lg font-semibold mb-4" style="color: var(--text-primary);">Upload Images</h2>
            
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
                    :class="isDragging ? 'border-primary-500 bg-primary-500/5' : ''"
                    class="flex flex-col items-center justify-center w-full py-10 border-2 border-dashed rounded-xl cursor-pointer transition-all duration-300 hover:border-primary-500/50"
                    :class="{ 'opacity-50 cursor-not-allowed': $wire.isProcessing }"
                    style="border-color: var(--border-color);"
                >
                    <!-- Normal State -->
                    <div x-show="!isCompressing && !$wire.isProcessing" class="flex flex-col items-center">
                        <div class="w-12 h-12 rounded-full bg-primary-500/10 flex items-center justify-center mb-3">
                            <svg class="w-6 h-6 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                        </div>
                        <p class="font-medium text-sm" style="color: var(--text-primary);">Drop images or click to upload</p>
                        <p class="text-xs mt-1" style="color: var(--text-secondary);">Multiple files • Stored locally in browser</p>
                    </div>
                    
                    <!-- Compressing State -->
                    <div x-show="isCompressing" x-cloak class="flex flex-col items-center py-2">
                        <div class="w-10 h-10 mb-3 relative">
                            <div class="absolute inset-0 rounded-full border-2 border-primary-500/20"></div>
                            <div class="absolute inset-0 rounded-full border-2 border-transparent border-t-primary-500 animate-spin"></div>
                        </div>
                        <p class="font-medium text-sm" style="color: var(--text-primary);">Processing images...</p>
                        <p class="text-xs mt-1" style="color: var(--text-secondary);"><span x-text="compressProgress"></span>/<span x-text="compressTotal"></span></p>
                    </div>
                </label>
            </div>

            <!-- Uploaded Images Grid -->
            <template x-if="localImages.length > 0">
                <div class="mt-5">
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-sm font-medium" style="color: var(--text-secondary);"><span x-text="localImages.length"></span> image(s) selected</span>
                        <button x-show="!$wire.isProcessing" @click="clearAllImages()" class="text-xs text-red-500 hover:text-red-400 transition-colors">Clear</button>
                    </div>
                    
                    <div class="grid grid-cols-5 gap-2">
                        <template x-for="(img, index) in localImages" :key="img.id">
                            <div class="relative aspect-square group rounded-lg overflow-hidden" style="background-color: var(--bg-hover);">
                                <img :src="img.thumbnail" alt="" class="w-full h-full object-cover">
                                
                                <!-- Status Overlay -->
                                <template x-if="getImageStatus(index) === 'processing'">
                                    <div class="absolute inset-0 bg-black/50 flex items-center justify-center">
                                        <div class="w-5 h-5 border-2 border-white/30 border-t-white rounded-full animate-spin"></div>
                                    </div>
                                </template>
                                <template x-if="getImageStatus(index) === 'completed'">
                                    <div class="absolute inset-0 bg-emerald-500/60 flex items-center justify-center backdrop-blur-[1px]">
                                        <div class="bg-white rounded-full p-1 shadow-lg">
                                            <svg class="w-5 h-5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                                            </svg>
                                        </div>
                                    </div>
                                </template>
                                <template x-if="getImageStatus(index) === 'failed'">
                                    <div class="absolute inset-0 bg-red-500/30 flex flex-col items-center justify-center gap-1">
                                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                        </svg>
                                        <button 
                                            @click="$wire.regenerateImage(index)"
                                            class="px-2 py-0.5 text-xs bg-white/90 hover:bg-white text-red-600 rounded font-medium transition-colors"
                                        >
                                            Retry
                                        </button>
                                    </div>
                                </template>
                                
                                <!-- Remove Button -->
                                <template x-if="!$wire.isProcessing && getImageStatus(index) === 'pending'">
                                    <button 
                                        @click="removeImage(index)"
                                        class="absolute top-1 right-1 w-5 h-5 bg-black/60 hover:bg-red-500 text-white rounded-full flex items-center justify-center opacity-0 group-hover:opacity-100 transition-all"
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

            <!-- Settings Section -->
            <template x-if="localImages.length > 0">
                <div class="mt-5 space-y-4">
                    <!-- Context Input -->
                    <input 
                        type="text"
                        wire:model="description"
                        class="input text-sm"
                        placeholder="Add context to improve results (optional)"
                        :disabled="$wire.isProcessing"
                    >
                    
                    <template x-if="!$wire.isProcessing">
                        <div class="space-y-4">
                            <!-- Mode Selection -->
                            <div>
                                <label class="text-xs font-medium uppercase tracking-wide mb-2 block" style="color: var(--text-muted);">Mode</label>
                                <div class="grid grid-cols-2 gap-2">
                                    <button 
                                        wire:click="$set('mode', 'fast')"
                                        class="p-3 rounded-xl text-left transition-all {{ $mode === 'fast' ? 'ring-2 ring-primary-500' : '' }}"
                                        style="background-color: var(--bg-hover);"
                                    >
                                        <div class="flex items-center gap-2 mb-1">
                                            <svg class="w-4 h-4 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                                            </svg>
                                            <span class="font-medium text-sm" style="color: var(--text-primary);">Fast</span>
                                        </div>
                                        <p class="text-xs" style="color: var(--text-secondary);">Quick SEO metadata</p>
                                    </button>
                                    <button 
                                        wire:click="$set('mode', 'slow')"
                                        class="p-3 rounded-xl text-left transition-all {{ $mode === 'slow' ? 'ring-2 ring-primary-500' : '' }}"
                                        style="background-color: var(--bg-hover);"
                                    >
                                        <div class="flex items-center gap-2 mb-1">
                                            <svg class="w-4 h-4 text-accent-cyan" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                                            </svg>
                                            <span class="font-medium text-sm" style="color: var(--text-primary);">Detailed</span>
                                        </div>
                                        <p class="text-xs" style="color: var(--text-secondary);">SEO + descriptive</p>
                                    </button>
                                </div>
                            </div>
                            
                            <!-- Keyword Count -->
                            <div>
                                <div class="flex items-center justify-between mb-2">
                                    <label class="text-xs font-medium uppercase tracking-wide" style="color: var(--text-muted);">Keywords</label>
                                    <span class="text-sm font-semibold text-primary-500">{{ $keywordCount }}</span>
                                </div>
                                <input 
                                    type="range" 
                                    wire:model.live="keywordCount" 
                                    min="10" 
                                    max="50" 
                                    step="5"
                                    class="w-full h-1.5 bg-primary-500/20 rounded-full appearance-none cursor-pointer"
                                >
                                <div class="flex justify-between mt-1">
                                    <span class="text-xs" style="color: var(--text-muted);">10</span>
                                    <span class="text-xs" style="color: var(--text-muted);">50</span>
                                </div>
                            </div>
                            
                            <!-- Delay Setting -->
                            <div>
                                <div class="flex items-center justify-between mb-2">
                                    <label class="text-xs font-medium uppercase tracking-wide" style="color: var(--text-muted);">Delay</label>
                                    <span class="text-sm font-semibold text-primary-500">{{ $delaySeconds }}s</span>
                                </div>
                                <input 
                                    type="range" 
                                    wire:model.live="delaySeconds" 
                                    min="1" 
                                    max="10" 
                                    step="1"
                                    class="w-full h-1.5 bg-primary-500/20 rounded-full appearance-none cursor-pointer"
                                >
                                <p class="text-xs mt-1" style="color: var(--text-muted);">Wait time between images</p>
                            </div>
                        </div>
                    </template>
                </div>
            </template>

            <!-- Progress -->
            @if($isProcessing)
                <div class="mt-5 p-4 rounded-xl" style="background-color: var(--bg-hover);" x-data="{ countdown: 0, isWaiting: false }" x-on:start-countdown.window="countdown = $event.detail.delay / 1000; isWaiting = true; const timer = setInterval(() => { countdown--; if(countdown <= 0) { clearInterval(timer); isWaiting = false; } }, 1000);">
                    <div class="flex items-center justify-between mb-3">
                        <div class="flex items-center gap-2">
                            <div class="w-4 h-4 border-2 border-primary-500/30 border-t-primary-500 rounded-full animate-spin"></div>
                            <span class="text-sm font-medium" style="color: var(--text-primary);">
                                <span x-show="!isWaiting">Processing image {{ $currentIndex + 1 }}...</span>
                                <span x-show="isWaiting" x-cloak>Waiting <span x-text="countdown"></span>s before next...</span>
                            </span>
                        </div>
                        <span class="text-sm font-semibold text-primary-500">{{ $processedCount }}/{{ $totalImages }}</span>
                    </div>
                    <div class="w-full h-1.5 rounded-full overflow-hidden" style="background-color: var(--bg-card);">
                        <div class="h-full bg-gradient-to-r from-primary-500 to-accent-cyan rounded-full transition-all duration-500" style="width: {{ $totalImages > 0 ? ($processedCount / $totalImages) * 100 : 0 }}%"></div>
                    </div>
                </div>
            @endif

            <!-- Action Button -->
            <template x-if="localImages.length > 0 && !$wire.isProcessing">
                <div class="mt-5">
                    <button 
                        @click="startGeneration()"
                        class="btn-primary w-full justify-center"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                        Generate<span x-show="localImages.length > 1"> (<span x-text="localImages.length"></span> images)</span>
                    </button>
                </div>
            </template>

            @if($error)
                <div class="mt-5 p-3 bg-red-500/10 border border-red-500/20 rounded-xl">
                    <p class="text-sm text-red-500">{{ $error }}</p>
                </div>
            @endif
        </div>

        <!-- Right Column: Results -->
        <div class="card">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-semibold" style="color: var(--text-primary);">Results</h2>
                @if(count($results) > 0 && !$isProcessing)
                    <button 
                        @click="exportToCSV()"
                        class="flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium rounded-lg bg-primary-500/10 text-primary-500 hover:bg-primary-500/20 transition-colors"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        Export CSV
                    </button>
                @endif
            </div>

            @if(count($results) > 0)
                <!-- Success Banner when all done -->
                @if(!$isProcessing && count($results) === $totalImages && $totalImages > 0)
                    <div class="mb-4 p-3 rounded-xl bg-emerald-500/10 border border-emerald-500/20">
                        <div class="flex items-center gap-2 text-emerald-500">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <span class="text-sm font-medium">All {{ $totalImages }} images processed!</span>
                        </div>
                    </div>
                @endif
                
                <div class="space-y-6 max-h-[800px] overflow-y-auto pr-2 custom-scrollbar">
                    @foreach($results as $index => $result)
                        <div class="group relative rounded-2xl border shadow-sm hover:shadow-md transition-all duration-300 overflow-hidden" 
                             style="background-color: var(--bg-card); border-color: var(--border-color);">
                            <!-- Item Header with Image & Actions -->
                            <div class="flex items-start gap-4 p-4 border-b" 
                                 style="border-color: var(--border-color); background-color: var(--bg-hover);">
                                <!-- Thumbnail -->
                                <div class="relative w-20 h-20 rounded-xl overflow-hidden shadow-sm flex-shrink-0 group-hover:scale-105 transition-transform duration-500"
                                     style="background-color: var(--bg-secondary);">
                                    <img x-show="getLocalImage({{ $index }})" :src="getLocalImage({{ $index }})?.thumbnail" alt="" class="w-full h-full object-cover">
                                    <div class="absolute inset-0 ring-1 ring-inset ring-black/10 dark:ring-white/10 rounded-xl"></div>
                                </div>
                                
                                <div class="flex-1 min-w-0 pt-1">
                                    <div class="flex items-start justify-between gap-2">
                                        <div>
                                            <h3 class="font-semibold truncate pr-4" 
                                                style="color: var(--text-primary);"
                                                title="{{ $result['filename'] }}">
                                                {{ $result['filename'] }}
                                            </h3>
                                            <div class="flex items-center gap-2 mt-1">
                                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-emerald-500/10 text-emerald-600 dark:text-emerald-400">
                                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                                    </svg>
                                                    Ready
                                                </span>
                                                <span class="text-xs" style="color: var(--text-secondary);">
                                                    {{ count(explode(',', $result['keywords'])) }} keywords
                                                </span>
                                            </div>
                                        </div>
                                        
                                        <!-- Top Actions -->
                                        <div class="flex items-center gap-1 opacity-100 lg:opacity-0 lg:group-hover:opacity-100 transition-opacity">
                                            <button 
                                                wire:click="copyToClipboard({{ $index }}, 'title')" 
                                                class="p-2 rounded-lg transition-colors hover:bg-primary-500/10 hover:text-primary-500"
                                                style="color: var(--text-secondary);"
                                                title="Copy Title"
                                            >
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3"/>
                                                </svg>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Content -->
                            <div class="p-4 space-y-4">
                                <!-- Title Section -->
                                <div class="space-y-2">
                                    <div class="flex items-center justify-between">
                                        <label class="text-xs font-bold uppercase tracking-wider" style="color: var(--text-muted);">Title</label>
                                        <button 
                                            wire:click="copyToClipboard({{ $index }}, 'title')"
                                            class="text-xs font-medium text-primary-500 hover:text-primary-600 flex items-center gap-1 transition-colors"
                                        >
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3"/>
                                            </svg>
                                            Copy
                                        </button>
                                    </div>
                                    <div class="p-3 rounded-xl border text-sm leading-relaxed" 
                                         style="background-color: var(--bg-hover); border-color: var(--border-color); color: var(--text-primary);">
                                        {{ $result['title'] }}
                                    </div>
                                </div>
                                
                                <!-- Keywords Section -->
                                <div class="space-y-2">
                                    <div class="flex items-center justify-between">
                                        <label class="text-xs font-bold uppercase tracking-wider" style="color: var(--text-muted);">Keywords</label>
                                        <button 
                                            wire:click="copyToClipboard({{ $index }}, 'keywords')"
                                            class="text-xs font-medium text-primary-500 hover:text-primary-600 flex items-center gap-1 transition-colors"
                                        >
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3"/>
                                            </svg>
                                            Copy
                                        </button>
                                    </div>
                                    <div class="flex flex-wrap gap-1.5">
                                        @foreach(explode(',', $result['keywords']) as $keyword)
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-medium bg-primary-500/10 text-primary-600 dark:text-primary-400 border border-primary-500/20 hover:bg-primary-500/20 transition-colors cursor-default">
                                                {{ trim($keyword) }}
                                            </span>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="flex flex-col items-center justify-center py-16 text-center">
                    <div class="w-14 h-14 rounded-full flex items-center justify-center mb-4" style="background-color: var(--bg-hover);">
                        <svg class="w-7 h-7" style="color: var(--text-secondary);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                        </svg>
                    </div>
                    <h3 class="font-medium mb-1" style="color: var(--text-primary);">No results yet</h3>
                    <p class="text-sm" style="color: var(--text-secondary);">Upload images and generate metadata</p>
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


