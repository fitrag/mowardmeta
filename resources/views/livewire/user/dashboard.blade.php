<div class="space-y-10">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold tracking-tight" style="color: var(--text-primary);">Dashboard</h1>
            <p class="mt-1 text-sm" style="color: var(--text-secondary);">Welcome back, {{ auth()->user()->name }}!</p>
        </div>
        <div class="flex items-center gap-3">
            <span class="text-xs font-medium px-3 py-1 rounded-full bg-emerald-500/10 text-emerald-500 border border-emerald-500/20">
                System Online
            </span>
        </div>
    </div>

    <!-- Stats Grid -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Total Generations -->
        <div class="p-6 rounded-2xl border transition-all duration-200 hover:shadow-lg hover:-translate-y-1" 
             style="background-color: var(--bg-card); border-color: var(--border-color);">
            <div class="flex items-center justify-between mb-4">
                <div class="w-10 h-10 rounded-xl bg-primary-500/10 flex items-center justify-center">
                    <svg class="w-5 h-5 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                </div>
                <span class="text-xs font-medium px-2 py-1 rounded-lg bg-primary-500/10 text-primary-500">Total</span>
            </div>
            <p class="text-3xl font-bold tracking-tight" style="color: var(--text-primary);">{{ number_format($totalGenerations) }}</p>
            <p class="text-sm mt-1" style="color: var(--text-secondary);">Images processed</p>
        </div>

        <!-- Today's Activity -->
        <div class="p-6 rounded-2xl border transition-all duration-200 hover:shadow-lg hover:-translate-y-1" 
             style="background-color: var(--bg-card); border-color: var(--border-color);">
            <div class="flex items-center justify-between mb-4">
                <div class="w-10 h-10 rounded-xl bg-accent-emerald/10 flex items-center justify-center">
                    <svg class="w-5 h-5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                </div>
                <span class="text-xs font-medium px-2 py-1 rounded-lg bg-emerald-500/10 text-emerald-500">Today</span>
            </div>
            <p class="text-3xl font-bold tracking-tight" style="color: var(--text-primary);">{{ number_format($todayGenerations) }}</p>
            <p class="text-sm mt-1" style="color: var(--text-secondary);">New generations</p>
        </div>

        <!-- Subscription Status -->
        <div class="p-6 rounded-2xl border transition-all duration-200 hover:shadow-lg hover:-translate-y-1" 
             style="background-color: var(--bg-card); border-color: var(--border-color);">
            <div class="flex items-center justify-between mb-4">
                <div class="w-10 h-10 rounded-xl {{ $isSubscribed ? 'bg-emerald-500/10' : 'bg-amber-500/10' }} flex items-center justify-center">
                    @if($isSubscribed)
                        <svg class="w-5 h-5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/>
                        </svg>
                    @else
                        <svg class="w-5 h-5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    @endif
                </div>
                <span class="text-xs font-medium px-2 py-1 rounded-lg {{ $isSubscribed ? 'bg-emerald-500/10 text-emerald-500' : 'bg-amber-500/10 text-amber-500' }}">
                    {{ $isSubscribed ? 'Subscribed' : 'Free' }}
                </span>
            </div>
            @if($isSubscribed)
                <p class="text-xl font-bold tracking-tight text-emerald-500">Unlimited</p>
                @if($subscriptionExpiresAt)
                    <p class="text-sm mt-1" style="color: var(--text-secondary);">Until {{ $subscriptionExpiresAt->format('d M Y') }}</p>
                @else
                    <p class="text-sm mt-1" style="color: var(--text-secondary);">Lifetime access</p>
                @endif
            @else
                <p class="text-xl font-bold tracking-tight text-amber-500">{{ $remainingGenerations }}/{{ $dailyLimit }}</p>
                <p class="text-sm mt-1" style="color: var(--text-secondary);">Remaining today</p>
                <a href="{{ route('subscription') }}" class="inline-flex items-center gap-1 text-xs font-medium text-primary-500 hover:text-primary-400 mt-2" wire:navigate>
                    Upgrade →
                </a>
            @endif
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Recent Activity -->
        <div class="lg:col-span-2 space-y-6">
            <div class="flex items-center justify-between">
                <h2 class="text-lg font-bold" style="color: var(--text-primary);">Recent Activity</h2>
                @if($recentGenerations->count() > 0)
                    <a href="{{ route('history') }}" class="text-sm font-medium text-primary-500 hover:text-primary-600 transition-colors" wire:navigate>
                        View History →
                    </a>
                @endif
            </div>

            @if($recentGenerations->count() > 0)
                <div class="space-y-4">
                    @foreach($recentGenerations as $generation)
                        <div class="group flex items-center gap-4 p-4 rounded-2xl border transition-all duration-200 hover:shadow-md hover:border-primary-500/30" 
                             style="background-color: var(--bg-card); border-color: var(--border-color);">
                            @if($generation->image_path)
                                <img src="{{ Storage::url($generation->image_path) }}" alt="" class="w-16 h-16 rounded-xl object-cover shadow-sm group-hover:scale-105 transition-transform duration-300">
                            @else
                                <div class="w-16 h-16 rounded-xl flex items-center justify-center" style="background-color: var(--bg-hover);">
                                    <svg class="w-6 h-6" style="color: var(--text-muted);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                </div>
                            @endif
                            
                            <div class="flex-1 min-w-0">
                                <h3 class="font-semibold truncate pr-4" style="color: var(--text-primary);">{{ $generation->filename ?? 'Untitled Image' }}</h3>
                                <p class="text-sm truncate pr-4 mt-0.5" style="color: var(--text-secondary);">{{ Str::limit($generation->title, 60) }}</p>
                            </div>
                            
                            <div class="text-right whitespace-nowrap">
                                <span class="text-xs font-medium px-2.5 py-1 rounded-lg" style="background-color: var(--bg-hover); color: var(--text-secondary);">
                                    {{ $generation->created_at->diffForHumans(null, true, true) }}
                                </span>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-12 rounded-3xl border border-dashed" style="border-color: var(--border-color);">
                    <div class="w-16 h-16 mx-auto rounded-2xl flex items-center justify-center mb-4" style="background-color: var(--bg-hover);">
                        <svg class="w-8 h-8" style="color: var(--text-muted);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <h3 class="font-bold text-lg mb-1" style="color: var(--text-primary);">No generations yet</h3>
                    <p class="text-sm mb-6 max-w-xs mx-auto" style="color: var(--text-secondary);">Start generating metadata for your images to see your history here.</p>
                    <a href="{{ route('generate') }}" class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl bg-primary-600 hover:bg-primary-500 text-white font-medium transition-colors shadow-lg shadow-primary-500/20" wire:navigate>
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        Start Generating
                    </a>
                </div>
            @endif
        </div>

        <!-- Quick Actions -->
        <div>
            <h2 class="text-lg font-bold mb-6" style="color: var(--text-primary);">Quick Actions</h2>
            <div class="space-y-4">
                <a href="{{ route('generate') }}" class="block p-5 rounded-2xl border transition-all duration-200 hover:border-primary-500 hover:shadow-lg group" 
                   style="background-color: var(--bg-card); border-color: var(--border-color);">
                    <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-primary-500 to-accent-cyan flex items-center justify-center mb-4 shadow-lg shadow-primary-500/20 group-hover:scale-110 transition-transform">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                    </div>
                    <h3 class="font-bold text-lg mb-1" style="color: var(--text-primary);">New Generation</h3>
                    <p class="text-sm" style="color: var(--text-secondary);">Upload images and generate metadata with AI</p>
                </a>

                <a href="{{ route('history') }}" class="block p-5 rounded-2xl border transition-all duration-200 hover:border-primary-500 hover:shadow-lg group" 
                   style="background-color: var(--bg-card); border-color: var(--border-color);">
                    <div class="w-12 h-12 rounded-xl flex items-center justify-center mb-4 group-hover:scale-110 transition-transform" style="background-color: var(--bg-hover);">
                        <svg class="w-6 h-6" style="color: var(--text-primary);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <h3 class="font-bold text-lg mb-1" style="color: var(--text-primary);">View History</h3>
                    <p class="text-sm" style="color: var(--text-secondary);">Browse and manage your past generations</p>
                </a>
            </div>
        </div>
    </div>
</div>
