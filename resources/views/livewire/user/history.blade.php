<div 
    class="space-y-6"
    x-data="clientHistoryManager()"
    x-init="initDB()"
>

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl lg:text-3xl font-bold" style="color: var(--text-primary);">History</h1>
            <p style="color: var(--text-secondary);" class="mt-1">Your generated metadata sessions (stored locally)</p>
        </div>

        <div class="flex flex-wrap gap-3">
            <template x-if="selectedSessions.length > 0">
                <button @click="deleteSelectedSessions()" class="btn-danger">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                    Delete (<span x-text="selectedSessions.length"></span>)
                </button>
            </template>
            
            <button @click="exportAllCsv()" class="btn-secondary" :disabled="sessions.length === 0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                </svg>
                Export All
            </button>
        </div>
    </div>

    <!-- Search -->
    <div class="card">
        <div class="relative">
            <svg class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5" style="color: var(--text-secondary);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
            <input 
                type="text" 
                x-model="search"
                @input.debounce.300ms="filterSessions()"
                placeholder="Search by filename, title, or keywords..."
                class="input pl-12"
            >
        </div>
    </div>

    <!-- Sessions List -->
    <div class="space-y-4">
        <template x-if="filteredSessions.length > 0">
            <div class="space-y-4">
                <template x-for="session in paginatedSessions" :key="session.id">
                    <div class="card p-0 overflow-hidden">
                        <!-- Session Header -->
                        <div class="flex items-center justify-between p-4" style="background-color: var(--bg-hover); border-bottom: 1px solid var(--border-color);">
                            <div class="flex items-center gap-3">
                                <input 
                                    type="checkbox" 
                                    :value="session.id"
                                    x-model="selectedSessions"
                                    class="w-4 h-4 rounded border-white/20 bg-surface-800 text-primary-500 focus:ring-primary-500/50"
                                >
                                <div>
                                    <p class="font-medium text-sm" style="color: var(--text-primary);">
                                        <span x-text="session.items.length"></span> image(s)
                                    </p>
                                    <p class="text-xs" style="color: var(--text-secondary);" x-text="formatDateTime(session.createdAt)"></p>
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                <button 
                                    @click="exportSessionCsv(session)"
                                    class="p-2 hover:bg-primary-500/20 rounded-lg text-primary-500 transition-colors"
                                    title="Export CSV"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                    </svg>
                                </button>
                                <button 
                                    @click="deleteSession(session.id)"
                                    class="p-2 hover:bg-red-500/20 rounded-lg text-red-400 transition-colors"
                                    title="Delete Session"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                </button>
                                <button 
                                    @click="session.expanded = !session.expanded"
                                    class="p-2 hover:bg-white/10 rounded-lg transition-colors"
                                    style="color: var(--text-secondary);"
                                >
                                    <svg class="w-4 h-4 transition-transform" :class="{ 'rotate-180': session.expanded }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                    </svg>
                                </button>
                            </div>
                        </div>
                        
                        <!-- Thumbnail Preview Row -->
                        <div class="p-4 flex gap-2 overflow-x-auto" style="border-bottom: 1px solid var(--border-color);">
                            <template x-for="item in session.items.slice(0, 10)" :key="item.id">
                                <div class="flex-shrink-0 w-12 h-12 rounded-lg overflow-hidden" style="background-color: var(--bg-hover);">
                                    <template x-if="item.thumbnail">
                                        <img :src="item.thumbnail" alt="" class="w-full h-full object-cover">
                                    </template>
                                    <template x-if="!item.thumbnail">
                                        <div class="w-full h-full flex items-center justify-center">
                                            <svg class="w-5 h-5" style="color: var(--text-muted);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                            </svg>
                                        </div>
                                    </template>
                                </div>
                            </template>
                            <template x-if="session.items.length > 10">
                                <div class="flex-shrink-0 w-12 h-12 rounded-lg flex items-center justify-center" style="background-color: var(--bg-hover);">
                                    <span class="text-xs font-medium" style="color: var(--text-secondary);">+<span x-text="session.items.length - 10"></span></span>
                                </div>
                            </template>
                        </div>
                        
                        <!-- Expanded Items -->
                        <div x-show="session.expanded" x-collapse>
                            <!-- Sort Controls -->
                            <div class="px-4 py-2 flex items-center justify-between text-xs" style="border-bottom: 1px solid var(--border-color); background-color: var(--bg-card);">
                                <span style="color: var(--text-secondary);">Sort by Filename:</span>
                                <div class="flex gap-1">
                                    <button 
                                        @click="sortSession(session.id, 'asc')"
                                        class="px-2 py-1 rounded transition-colors font-medium"
                                        :class="session.sortDir === 'asc' ? 'bg-primary-500 text-white' : 'hover:bg-white/10'"
                                        :style="session.sortDir !== 'asc' ? 'color: var(--text-secondary);' : ''"
                                    >ASC</button>
                                    <button 
                                        @click="sortSession(session.id, 'desc')"
                                        class="px-2 py-1 rounded transition-colors font-medium"
                                        :class="session.sortDir === 'desc' ? 'bg-primary-500 text-white' : 'hover:bg-white/10'"
                                        :style="session.sortDir !== 'desc' ? 'color: var(--text-secondary);' : ''"
                                    >DESC</button>
                                </div>
                            </div>
                            <div class="divide-y" style="border-color: var(--border-color);">
                                <template x-for="item in session.items" :key="item.id">
                                    <div class="p-4 hover:bg-white/5 transition-colors" style="border-color: var(--border-color);">
                                        <div class="flex gap-4">
                                            <!-- Thumbnail -->
                                            <div class="flex-shrink-0 w-16 h-16 rounded-lg overflow-hidden" style="background-color: var(--bg-hover);">
                                                <template x-if="item.thumbnail">
                                                    <img :src="item.thumbnail" alt="" class="w-full h-full object-cover">
                                                </template>
                                                <template x-if="!item.thumbnail">
                                                    <div class="w-full h-full flex items-center justify-center">
                                                        <svg class="w-6 h-6" style="color: var(--text-muted);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                                        </svg>
                                                    </div>
                                                </template>
                                            </div>
                                            
                                            <!-- Content -->
                                            <div class="flex-1 min-w-0">
                                                <div class="flex items-start justify-between gap-2 mb-2">
                                                    <p class="font-medium text-sm truncate" style="color: var(--text-primary);" x-text="item.filename"></p>
                                                    <button 
                                                        @click="copyItem(item)"
                                                        class="flex-shrink-0 text-xs text-primary-500 hover:text-primary-400"
                                                    >Copy</button>
                                                </div>
                                                
                                                <!-- Title -->
                                                <p class="text-sm mb-2" style="color: var(--text-secondary);" x-text="truncate(item.title, 100)"></p>
                                                
                                                <!-- Keywords Preview -->
                                                <div class="flex flex-wrap gap-1">
                                                    <template x-for="keyword in getKeywords(item.keywords, 5)" :key="keyword">
                                                        <span class="px-2 py-0.5 bg-primary-500/10 text-primary-500 rounded text-xs" x-text="keyword"></span>
                                                    </template>
                                                    <template x-if="countKeywords(item.keywords) > 5">
                                                        <span class="text-xs" style="color: var(--text-muted);">+<span x-text="countKeywords(item.keywords) - 5"></span></span>
                                                    </template>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>
                </template>
                
                <!-- Pagination -->
                <template x-if="totalPages > 1">
                    <div class="flex items-center justify-between">
                        <p class="text-sm" style="color: var(--text-secondary);">
                            Showing <span x-text="((currentPage - 1) * perPage) + 1"></span> to <span x-text="Math.min(currentPage * perPage, filteredSessions.length)"></span> of <span x-text="filteredSessions.length"></span> sessions
                        </p>
                        <div class="flex gap-2">
                            <button 
                                @click="prevPage()"
                                :disabled="currentPage === 1"
                                class="px-3 py-1.5 text-sm rounded-lg transition-colors"
                                :class="currentPage === 1 ? 'opacity-50 cursor-not-allowed' : 'hover:bg-white/10'"
                                style="background-color: var(--bg-hover);"
                            >Previous</button>
                            <button 
                                @click="nextPage()"
                                :disabled="currentPage >= totalPages"
                                class="px-3 py-1.5 text-sm rounded-lg transition-colors"
                                :class="currentPage >= totalPages ? 'opacity-50 cursor-not-allowed' : 'hover:bg-white/10'"
                                style="background-color: var(--bg-hover);"
                            >Next</button>
                        </div>
                    </div>
                </template>
            </div>
        </template>

        <template x-if="filteredSessions.length === 0 && !isLoading">
            <div class="card">
                <div class="flex flex-col items-center justify-center py-16 text-center">
                    <div class="w-16 h-16 rounded-full flex items-center justify-center mb-4" style="background-color: var(--bg-hover);">
                        <svg class="w-8 h-8" style="color: var(--text-secondary);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <h3 class="font-medium mb-1" style="color: var(--text-primary);">No history found</h3>
                    <p class="text-sm mb-4" style="color: var(--text-secondary);">
                        <template x-if="search">
                            <span>No results match your search</span>
                        </template>
                        <template x-if="!search">
                            <span>Start generating metadata to see your history here</span>
                        </template>
                    </p>
                    <template x-if="!search">
                        <a href="{{ route('generate') }}" class="btn-primary" wire:navigate>
                            Generate Metadata
                        </a>
                    </template>
                </div>
            </div>
        </template>

        <template x-if="isLoading">
            <div class="card">
                <div class="flex items-center justify-center py-16">
                    <div class="w-8 h-8 border-2 border-primary-500/30 border-t-primary-500 rounded-full animate-spin"></div>
                </div>
            </div>
        </template>
    </div>
</div>


