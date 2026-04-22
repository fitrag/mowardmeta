<div 
    class="space-y-6" 
    x-data="clientHistoryManager()"
    x-init="initDB()"
>
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div class="section-header mb-0">
            <h1>History</h1>
            <p>Your generated metadata sessions</p>
        </div>

        <div class="flex flex-wrap gap-2">
            <template x-if="selectedSessions.length > 0">
                <button @click="deleteSelectedSessions()" class="btn-danger">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                    Delete (<span x-text="selectedSessions.length"></span>)
                </button>
            </template>
            
            <button @click="exportAllCsv()" class="btn-secondary" :disabled="sessions.length === 0">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                </svg>
                Export All
            </button>
        </div>
    </div>

    <!-- Stats Summary -->
    <div class="grid grid-cols-3 gap-3" x-show="!isLoading && sessions.length > 0">
        <div class="card p-3">
            <p class="text-[11px] font-medium uppercase tracking-wide" style="color: var(--text-muted);">Sessions</p>
            <p class="text-xl font-bold mt-0.5" style="color: var(--text-primary);" x-text="sessions.length"></p>
        </div>
        <div class="card p-3">
            <p class="text-[11px] font-medium uppercase tracking-wide" style="color: var(--text-muted);">Total Images</p>
            <p class="text-xl font-bold mt-0.5" style="color: var(--text-primary);" x-text="sessions.reduce((acc, s) => acc + s.items.length, 0)"></p>
        </div>
        <div class="card p-3">
            <p class="text-[11px] font-medium uppercase tracking-wide" style="color: var(--text-muted);">Storage</p>
            <p class="text-xl font-bold mt-0.5" style="color: var(--text-primary);">Local</p>
        </div>
    </div>

    <!-- Search -->
    <div class="card">
        <div class="relative">
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4" style="color: var(--text-muted);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
            <input 
                type="text" 
                x-model="search"
                @input.debounce.300ms="filterSessions()"
                placeholder="Search by filename, title, or keywords..."
                class="input pl-10"
            >
        </div>
    </div>

    <!-- Sessions List -->
    <div class="space-y-4">
        <template x-if="filteredSessions.length > 0">
            <div class="space-y-3">
                <template x-for="session in paginatedSessions" :key="session.id">
                    <div class="card p-0 overflow-hidden transition-all hover:border-[var(--border-color-strong)]">
                        <!-- Session Header -->
                        <div class="flex items-center justify-between p-4" style="background-color: var(--bg-muted); border-bottom: 1px solid var(--border-color);">
                            <div class="flex items-center gap-3">
                                <input 
                                    type="checkbox" 
                                    :value="session.id"
                                    x-model="selectedSessions"
                                    class="w-4 h-4 rounded transition-colors"
                                    style="border-color: var(--border-color); background-color: var(--bg-input);"
                                >
                                <div class="flex items-center gap-2">
                                    <div class="w-8 h-8 rounded-lg flex items-center justify-center" style="background-color: var(--accent-muted);">
                                        <svg class="w-4 h-4" style="color: var(--accent);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium" style="color: var(--text-primary);">
                                            <span x-text="session.items.length"></span> image(s)
                                        </p>
                                        <p class="text-[11px]" style="color: var(--text-muted);" x-text="formatDateTime(session.createdAt)"></p>
                                    </div>
                                </div>
                            </div>
                            <div class="flex items-center gap-1">
                                <button 
                                    @click="exportSessionCsv(session)"
                                    class="p-2 rounded-lg transition-colors"
                                    style="color: var(--text-muted);"
                                    onmouseover="this.style.backgroundColor='var(--accent-muted)'; this.style.color='var(--accent)'"
                                    onmouseout="this.style.backgroundColor='transparent'; this.style.color='var(--text-muted)'"
                                    title="Export CSV"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                    </svg>
                                </button>
                                <button 
                                    @click="deleteSession(session.id)"
                                    class="p-2 rounded-lg transition-colors"
                                    style="color: var(--text-muted);"
                                    onmouseover="this.style.backgroundColor='var(--danger-muted)'; this.style.color='var(--danger)'"
                                    onmouseout="this.style.backgroundColor='transparent'; this.style.color='var(--text-muted)'"
                                    title="Delete Session"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                </button>
                                <button 
                                    @click="session.expanded = !session.expanded"
                                    class="p-2 rounded-lg transition-colors"
                                    style="color: var(--text-muted);"
                                    onmouseover="this.style.backgroundColor='var(--bg-muted)'"
                                    onmouseout="this.style.backgroundColor='transparent'"
                                >
                                    <svg class="w-4 h-4 transition-transform duration-200" :class="{ 'rotate-180': session.expanded }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                    </svg>
                                </button>
                            </div>
                        </div>
                        
                        <!-- Thumbnail Strip -->
                        <div class="p-3 flex gap-2 overflow-x-auto" style="border-bottom: 1px solid var(--border-color);">
                            <template x-for="item in session.items.slice(0, 8)" :key="item.id">
                                <div class="flex-shrink-0 w-12 h-12 rounded-lg overflow-hidden ring-1" style="background-color: var(--bg-muted); ring-color: var(--border-color);">
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
                            <template x-if="session.items.length > 8">
                                <div class="flex-shrink-0 w-12 h-12 rounded-lg flex items-center justify-center" style="background-color: var(--bg-muted); border: 1px solid var(--border-color);">
                                    <span class="text-[11px] font-medium" style="color: var(--text-muted);">+<span x-text="session.items.length - 8"></span></span>
                                </div>
                            </template>
                        </div>
                        
                        <!-- Expanded Items -->
                        <div x-show="session.expanded" x-collapse>
                            <div class="px-4 py-2 flex items-center justify-between" style="border-bottom: 1px solid var(--border-color); background-color: var(--bg-card);">
                                <span class="text-[11px] font-medium" style="color: var(--text-muted);">Sort by Filename</span>
                                <div class="flex gap-1">
                                    <button 
                                        @click="sortSession(session.id, 'asc')"
                                        class="px-2.5 py-1 rounded-md text-[11px] font-medium transition-colors"
                                        :class="session.sortDir === 'asc' ? 'text-white' : ''"
                                        :style="session.sortDir === 'asc' ? 'background-color: var(--accent);' : 'background-color: var(--bg-muted); color: var(--text-muted);'"
                                    >A-Z</button>
                                    <button 
                                        @click="sortSession(session.id, 'desc')"
                                        class="px-2.5 py-1 rounded-md text-[11px] font-medium transition-colors"
                                        :class="session.sortDir === 'desc' ? 'text-white' : ''"
                                        :style="session.sortDir === 'desc' ? 'background-color: var(--accent);' : 'background-color: var(--bg-muted); color: var(--text-muted);'"
                                    >Z-A</button>
                                </div>
                            </div>
                            <div class="divide-y" style="border-color: var(--border-color);">
                                <template x-for="item in session.items" :key="item.id">
                                    <div class="p-4 transition-colors group" style="border-color: var(--border-color);" onmouseover="this.style.backgroundColor='var(--bg-muted)'" onmouseout="this.style.backgroundColor='transparent'">
                                        <div class="flex gap-3">
                                            <div class="flex-shrink-0 w-14 h-14 rounded-xl overflow-hidden ring-1" style="background-color: var(--bg-muted); ring-color: var(--border-color);">
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
                                            
                                            <div class="flex-1 min-w-0">
                                                <div class="flex items-start justify-between gap-2 mb-1">
                                                    <p class="text-sm font-medium truncate" style="color: var(--text-primary);" x-text="item.filename"></p>
                                                    <button 
                                                        @click="copyItem(item)"
                                                        class="flex-shrink-0 text-[11px] font-medium px-2 py-1 rounded-md transition-colors opacity-0 group-hover:opacity-100"
                                                        style="color: var(--accent);"
                                                        onmouseover="this.style.backgroundColor='var(--accent-muted)'"
                                                        onmouseout="this.style.backgroundColor='transparent'"
                                                    >
                                                        Copy
                                                    </button>
                                                </div>
                                                
                                                <p class="text-xs mb-2 line-clamp-2" style="color: var(--text-secondary);" x-text="truncate(item.title, 120)"></p>
                                                
                                                <div class="flex flex-wrap gap-1">
                                                    <template x-for="keyword in getKeywords(item.keywords, 6)" :key="keyword">
                                                        <span class="tag" x-text="keyword"></span>
                                                    </template>
                                                    <template x-if="countKeywords(item.keywords) > 6">
                                                        <span class="text-[11px] px-1.5" style="color: var(--text-muted);">+<span x-text="countKeywords(item.keywords) - 6"></span></span>
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
                    <div class="flex items-center justify-between pt-2">
                        <p class="text-xs" style="color: var(--text-muted);">
                            <span x-text="filteredSessions.length"></span> sessions total
                        </p>
                        <div class="flex items-center gap-1">
                            <button 
                                @click="prevPage()"
                                :disabled="currentPage === 1"
                                class="p-2 rounded-lg transition-colors"
                                :class="currentPage === 1 ? 'opacity-50 cursor-not-allowed' : 'hover:bg-[var(--bg-muted)]'"
                                style="color: var(--text-secondary);"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                                </svg>
                            </button>
                            <span class="text-xs px-3 py-1 rounded-lg" style="background-color: var(--bg-muted); color: var(--text-primary);">
                                <span x-text="currentPage"></span> / <span x-text="totalPages"></span>
                            </span>
                            <button 
                                @click="nextPage()"
                                :disabled="currentPage >= totalPages"
                                class="p-2 rounded-lg transition-colors"
                                :class="currentPage >= totalPages ? 'opacity-50 cursor-not-allowed' : 'hover:bg-[var(--bg-muted)]'"
                                style="color: var(--text-secondary);"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                </template>
            </div>
        </template>

        <!-- Empty State -->
        <template x-if="filteredSessions.length === 0 && !isLoading">
            <div class="card">
                <div class="empty-state">
                    <div class="empty-state-icon">
                        <svg class="w-6 h-6" style="color: var(--text-muted);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <h3>No history found</h3>
                    <p class="text-center max-w-xs">
                        <template x-if="search">
                            <span>No results match your search. Try different keywords.</span>
                        </template>
                        <template x-if="!search">
                            <span>Start generating metadata to see your history here</span>
                        </template>
                    </p>
                    <template x-if="!search">
                        <a href="{{ route('generate') }}" class="btn-primary" wire:navigate>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                            </svg>
                            Generate Metadata
                        </a>
                    </template>
                </div>
            </div>
        </template>

        <!-- Loading -->
        <template x-if="isLoading">
            <div class="card">
                <div class="flex items-center justify-center py-16">
                    <div class="w-6 h-6 border-2 border-[var(--border-color-strong)] border-t-[var(--accent)] rounded-full animate-spin"></div>
                </div>
            </div>
        </template>
    </div>
</div>
