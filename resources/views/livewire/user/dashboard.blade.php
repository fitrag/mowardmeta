<div class="space-y-6">
    <!-- Hero Section -->
    <div class="relative overflow-hidden rounded-xl p-6" style="background: linear-gradient(135deg, var(--accent) 0%, #06b6d4 100%);">
        <div class="relative z-10">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-xs font-medium text-white/70 mb-1">Welcome back</p>
                    <h1 class="text-xl font-bold text-white">{{ auth()->user()->name }}</h1>
                    <p class="text-xs text-white/70 mt-1">
                        @if($isSubscribed)
                            Unlimited generations with full access
                        @else
                            {{ $remainingGenerations }} generations remaining today
                        @endif
                    </p>
                </div>
                <div class="w-10 h-10 rounded-xl bg-white/20 backdrop-blur-sm flex items-center justify-center">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                </div>
            </div>
            
            @if(!$isSubscribed)
                <div class="mt-4" x-data="{ progress: {{ min(($todayGenerations / max($dailyLimit, 1)) * 100, 100) }} }">
                    <div class="flex items-center justify-between text-[11px] text-white/80 mb-1.5">
                        <span>Daily Limit</span>
                        <span>{{ $todayGenerations }} / {{ $dailyLimit }}</span>
                    </div>
                    <div class="w-full h-1.5 rounded-full bg-white/20 overflow-hidden">
                        <div class="h-full rounded-full bg-white/90 transition-all duration-500" x-bind:style="'width: ' + progress + '%'"></div>
                    </div>
                </div>
            @endif
        </div>
        
        <!-- Decorative circles -->
        <div class="absolute -top-8 -right-8 w-32 h-32 rounded-full bg-white/5"></div>
        <div class="absolute -bottom-10 -left-10 w-40 h-40 rounded-full bg-white/5"></div>
    </div>

    <!-- Stats Grid -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="card group hover:border-[var(--border-color-strong)] transition-colors">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-[11px] font-medium uppercase tracking-wide" style="color: var(--text-muted);">Total Generations</p>
                    <p class="text-2xl font-bold mt-1" style="color: var(--text-primary);">{{ number_format($totalGenerations) }}</p>
                </div>
                <div class="w-9 h-9 rounded-lg flex items-center justify-center" style="background-color: var(--accent-muted);">
                    <svg class="w-4 h-4" style="color: var(--accent);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                </div>
            </div>
            <div class="mt-3 flex items-center gap-1.5 text-[11px]" style="color: var(--text-muted);">
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                </svg>
                <span>All time</span>
            </div>
        </div>

        <div class="card group hover:border-[var(--border-color-strong)] transition-colors">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-[11px] font-medium uppercase tracking-wide" style="color: var(--text-muted);">Today</p>
                    <p class="text-2xl font-bold mt-1" style="color: var(--text-primary);">{{ number_format($todayGenerations) }}</p>
                </div>
                <div class="w-9 h-9 rounded-lg flex items-center justify-center" style="background-color: var(--success-muted);">
                    <svg class="w-4 h-4" style="color: var(--success);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                </div>
            </div>
            <div class="mt-3 flex items-center gap-1.5 text-[11px]" style="color: var(--text-muted);">
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span>{{ now()->format('l, d M Y') }}</span>
            </div>
        </div>

        <div class="card group hover:border-[var(--border-color-strong)] transition-colors">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-[11px] font-medium uppercase tracking-wide" style="color: var(--text-muted);">Status</p>
                    @if($isSubscribed)
                        <p class="text-2xl font-bold mt-1" style="color: var(--success);">Unlimited</p>
                    @else
                        <p class="text-2xl font-bold mt-1" style="color: var(--warning);">Free</p>
                    @endif
                </div>
                @if($isSubscribed)
                    <div class="w-9 h-9 rounded-lg flex items-center justify-center" style="background-color: var(--success-muted);">
                        <svg class="w-4 h-4" style="color: var(--success);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/>
                        </svg>
                    </div>
                @else
                    <div class="w-9 h-9 rounded-lg flex items-center justify-center" style="background-color: var(--warning-muted);">
                        <svg class="w-4 h-4" style="color: var(--warning);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                @endif
            </div>
            <div class="mt-3 flex items-center gap-1.5 text-[11px]" style="color: var(--text-muted);">
                @if($isSubscribed)
                    @if($subscriptionExpiresAt)
                        <span>Until {{ $subscriptionExpiresAt->format('d M Y') }}</span>
                    @else
                        <span>Lifetime access</span>
                    @endif
                @else
                    <span>{{ $remainingGenerations }} / {{ $dailyLimit }} remaining</span>
                @endif
            </div>
        </div>
    </div>

    <!-- Main Content Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
        <!-- Recent Activity -->
        <div class="lg:col-span-2">
            <div class="card">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h2 class="text-sm font-semibold" style="color: var(--text-primary);">Recent Activity</h2>
                        <p class="text-[11px] mt-0.5" style="color: var(--text-muted);">Your latest metadata generations</p>
                    </div>
                    @if($recentGenerations->count() > 0)
                        <a href="{{ route('history') }}" class="btn-ghost text-xs" wire:navigate>
                            View All
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </a>
                    @endif
                </div>

                @if($recentGenerations->count() > 0)
                    <div class="space-y-1.5">
                        @foreach($recentGenerations as $generation)
                            <a href="{{ route('history') }}" class="flex items-center gap-3 p-2.5 rounded-lg transition-colors group" style="background-color: var(--bg-muted);" onmouseover="this.style.backgroundColor='var(--bg-card)'" onmouseout="this.style.backgroundColor='var(--bg-muted)'" wire:navigate>
                                @if($generation->image_path)
                                    <img src="{{ Storage::url($generation->image_path) }}" alt="" class="w-10 h-10 rounded-lg object-cover flex-shrink-0">
                                @else
                                    <div class="w-10 h-10 rounded-lg flex items-center justify-center flex-shrink-0" style="background-color: var(--bg-card); border: 1px solid var(--border-color);">
                                        <svg class="w-4 h-4" style="color: var(--text-muted);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                        </svg>
                                    </div>
                                @endif
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium truncate" style="color: var(--text-primary);">{{ $generation->filename ?? 'Untitled' }}</p>
                                    <p class="text-xs truncate" style="color: var(--text-muted);">{{ Str::limit($generation->title, 50) }}</p>
                                </div>
                                <div class="flex items-center gap-1.5 flex-shrink-0">
                                    <span class="text-[11px]" style="color: var(--text-muted);">{{ $generation->created_at->diffForHumans(null, true, true) }}</span>
                                    <svg class="w-3 h-3 opacity-0 group-hover:opacity-100 transition-opacity" style="color: var(--text-muted);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                    </svg>
                                </div>
                            </a>
                        @endforeach
                    </div>
                @else
                    <div class="empty-state">
                        <div class="empty-state-icon">
                            <svg class="w-6 h-6" style="color: var(--text-muted);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                        </div>
                        <h3>No generations yet</h3>
                        <p>Start generating metadata for your images</p>
                        <a href="{{ route('generate') }}" class="btn-primary" wire:navigate>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                            Start Generating
                        </a>
                    </div>
                @endif
            </div>
        </div>

        <!-- Right Column -->
        <div class="space-y-4">
            <!-- Quick Actions -->
            <div>
                <h2 class="text-sm font-semibold mb-3" style="color: var(--text-primary);">Quick Actions</h2>
                <div class="space-y-2">
                    <a href="{{ route('generate') }}" class="flex items-center gap-3 p-3 rounded-xl border transition-all hover:scale-[1.02] active:scale-[0.98]" style="background-color: var(--bg-card); border-color: var(--border-color);" wire:navigate>
                        <div class="w-10 h-10 rounded-lg flex items-center justify-center" style="background: linear-gradient(135deg, var(--accent), #06b6d4);">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                            </svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <h3 class="text-sm font-semibold" style="color: var(--text-primary);">New Generation</h3>
                            <p class="text-[11px]" style="color: var(--text-muted);">Upload images & generate metadata</p>
                        </div>
                        <svg class="w-4 h-4 flex-shrink-0" style="color: var(--text-muted);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </a>

                    <a href="{{ route('keywords') }}" class="flex items-center gap-3 p-3 rounded-xl border transition-all hover:scale-[1.02] active:scale-[0.98]" style="background-color: var(--bg-card); border-color: var(--border-color);" wire:navigate>
                        <div class="w-10 h-10 rounded-lg flex items-center justify-center" style="background-color: var(--accent-muted);">
                            <svg class="w-5 h-5" style="color: var(--accent);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                            </svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <h3 class="text-sm font-semibold" style="color: var(--text-primary);">Keyword Generator</h3>
                            <p class="text-[11px]" style="color: var(--text-muted);">Generate keywords from title</p>
                        </div>
                        <svg class="w-4 h-4 flex-shrink-0" style="color: var(--text-muted);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </a>

                    <a href="{{ route('history') }}" class="flex items-center gap-3 p-3 rounded-xl border transition-all hover:scale-[1.02] active:scale-[0.98]" style="background-color: var(--bg-card); border-color: var(--border-color);" wire:navigate>
                        <div class="w-10 h-10 rounded-lg flex items-center justify-center" style="background-color: var(--bg-muted);">
                            <svg class="w-5 h-5" style="color: var(--text-secondary);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <h3 class="text-sm font-semibold" style="color: var(--text-primary);">View History</h3>
                            <p class="text-[11px]" style="color: var(--text-muted);">Browse past generations</p>
                        </div>
                        <svg class="w-4 h-4 flex-shrink-0" style="color: var(--text-muted);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </a>
                </div>
            </div>

            <!-- Tips Card -->
            <div class="card" style="border-color: var(--accent-muted);">
                <div class="flex items-center gap-2 mb-3">
                    <div class="w-7 h-7 rounded-lg flex items-center justify-center" style="background-color: var(--accent-muted);">
                        <svg class="w-3.5 h-3.5" style="color: var(--accent);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <h3 class="text-sm font-semibold" style="color: var(--text-primary);">Pro Tip</h3>
                </div>
                <p class="text-xs leading-relaxed" style="color: var(--text-secondary);">
                    Use high-quality images with clear subjects for the best metadata generation results. The AI analyzes visual elements to create accurate titles and keywords.
                </p>
            </div>
        </div>
    </div>
</div>
