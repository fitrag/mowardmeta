<div class="space-y-6 sm:space-y-10">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold tracking-tight" style="color: var(--text-primary);">Dashboard</h1>
            <p class="mt-1 text-sm" style="color: var(--text-secondary);">Welcome back, {{ auth()->user()->name }}!</p>
        </div>
        <div class="flex items-center gap-3">
            <span class="text-xs font-medium px-3 py-1 rounded-full bg-emerald-500/10 text-emerald-500 border border-emerald-500/20">
                System Online
            </span>
        </div>
    </div>

    <!-- Stats Grid -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-6">
        <!-- Total Generations -->
        <div class="p-4 sm:p-6 rounded-2xl border transition-all duration-200 hover:shadow-lg hover:-translate-y-1" 
             style="background-color: var(--bg-card); border-color: var(--border-color);">
            <div class="flex items-center justify-between mb-3 sm:mb-4">
                <div class="w-8 h-8 sm:w-10 sm:h-10 rounded-xl bg-primary-500/10 flex items-center justify-center">
                    <svg class="w-4 h-4 sm:w-5 sm:h-5 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                </div>
                <span class="hidden sm:inline text-xs font-medium px-2 py-1 rounded-lg bg-primary-500/10 text-primary-500">Total</span>
            </div>
            <p class="text-2xl sm:text-3xl font-bold tracking-tight" style="color: var(--text-primary);">{{ number_format($totalGenerations) }}</p>
            <p class="text-xs sm:text-sm mt-1" style="color: var(--text-secondary);">Images processed</p>
        </div>

        <!-- Today's Activity -->
        <div class="p-4 sm:p-6 rounded-2xl border transition-all duration-200 hover:shadow-lg hover:-translate-y-1" 
             style="background-color: var(--bg-card); border-color: var(--border-color);">
            <div class="flex items-center justify-between mb-3 sm:mb-4">
                <div class="w-8 h-8 sm:w-10 sm:h-10 rounded-xl bg-emerald-500/10 flex items-center justify-center">
                    <svg class="w-4 h-4 sm:w-5 sm:h-5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                </div>
                <span class="hidden sm:inline text-xs font-medium px-2 py-1 rounded-lg bg-emerald-500/10 text-emerald-500">Today</span>
            </div>
            <p class="text-2xl sm:text-3xl font-bold tracking-tight" style="color: var(--text-primary);">{{ number_format($todayGenerations) }}</p>
            <p class="text-xs sm:text-sm mt-1" style="color: var(--text-secondary);">New generations</p>
        </div>

        <!-- Active Licenses -->
        <a href="{{ route('licenses') }}" class="p-4 sm:p-6 rounded-2xl border transition-all duration-200 hover:shadow-lg hover:-translate-y-1 block" 
           style="background-color: var(--bg-card); border-color: var(--border-color);" wire:navigate>
            <div class="flex items-center justify-between mb-3 sm:mb-4">
                <div class="w-8 h-8 sm:w-10 sm:h-10 rounded-xl bg-purple-500/10 flex items-center justify-center">
                    <svg class="w-4 h-4 sm:w-5 sm:h-5 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                    </svg>
                </div>
                <span class="hidden sm:inline text-xs font-medium px-2 py-1 rounded-lg bg-purple-500/10 text-purple-500">Licenses</span>
            </div>
            <p class="text-2xl sm:text-3xl font-bold tracking-tight {{ $activeLicenses->count() > 0 ? 'text-purple-500' : '' }}" style="{{ $activeLicenses->count() == 0 ? 'color: var(--text-primary);' : '' }}">{{ $activeLicenses->count() }}</p>
            <p class="text-xs sm:text-sm mt-1" style="color: var(--text-secondary);">Active licenses</p>
        </a>

        <!-- My Products -->
        <a href="{{ route('products') }}" class="p-4 sm:p-6 rounded-2xl border transition-all duration-200 hover:shadow-lg hover:-translate-y-1 block" 
           style="background-color: var(--bg-card); border-color: var(--border-color);" wire:navigate>
            <div class="flex items-center justify-between mb-3 sm:mb-4">
                <div class="w-8 h-8 sm:w-10 sm:h-10 rounded-xl bg-cyan-500/10 flex items-center justify-center">
                    <svg class="w-4 h-4 sm:w-5 sm:h-5 text-cyan-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                    </svg>
                </div>
                <span class="hidden sm:inline text-xs font-medium px-2 py-1 rounded-lg bg-cyan-500/10 text-cyan-500">Products</span>
            </div>
            <p class="text-2xl sm:text-3xl font-bold tracking-tight {{ $myProductsCount > 0 ? 'text-cyan-500' : '' }}" style="{{ $myProductsCount == 0 ? 'color: var(--text-primary);' : '' }}">{{ $myProductsCount }}</p>
            <p class="text-xs sm:text-sm mt-1" style="color: var(--text-secondary);">My products</p>
        </a>
    </div>

    <!-- Subscription Banner -->
    <div class="p-4 sm:p-6 rounded-2xl border {{ $isSubscribed ? 'border-emerald-500/30 bg-emerald-500/5' : 'border-amber-500/30 bg-amber-500/5' }}">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="flex items-center gap-3 sm:gap-4">
                <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-xl {{ $isSubscribed ? 'bg-emerald-500/20' : 'bg-amber-500/20' }} flex items-center justify-center flex-shrink-0">
                    @if($isSubscribed)
                        <svg class="w-5 h-5 sm:w-6 sm:h-6 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/>
                        </svg>
                    @else
                        <svg class="w-5 h-5 sm:w-6 sm:h-6 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    @endif
                </div>
                <div>
                    @if($isSubscribed)
                        <p class="font-bold text-emerald-500">Premium Subscription</p>
                        @if($subscriptionExpiresAt)
                            <p class="text-xs sm:text-sm" style="color: var(--text-secondary);">Valid until {{ $subscriptionExpiresAt->format('d M Y') }}</p>
                        @else
                            <p class="text-xs sm:text-sm" style="color: var(--text-secondary);">Lifetime access</p>
                        @endif
                    @else
                        <p class="font-bold text-amber-500">Free Plan</p>
                        <p class="text-xs sm:text-sm" style="color: var(--text-secondary);">{{ $remainingGenerations }}/{{ $dailyLimit }} generations remaining today</p>
                    @endif
                </div>
            </div>
            @if(!$isSubscribed)
                <a href="{{ route('subscription') }}" class="btn-primary text-sm w-full sm:w-auto text-center" wire:navigate>
                    Upgrade Now
                </a>
            @endif
        </div>
    </div>

    <!-- Active Licenses Widget -->
    @if($activeLicenses->count() > 0)
        <div class="card">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-base sm:text-lg font-bold" style="color: var(--text-primary);">Active Licenses</h2>
                <a href="{{ route('licenses') }}" class="text-xs sm:text-sm font-medium text-primary-500 hover:text-primary-400" wire:navigate>View All →</a>
            </div>
            <div class="space-y-3">
                @foreach($activeLicenses->take(3) as $license)
                    <div class="p-3 sm:p-4 rounded-xl flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 sm:gap-4" style="background-color: var(--bg-hover);">
                        <div class="flex-1 min-w-0">
                            <div class="flex flex-wrap items-center gap-2">
                                <p class="font-medium text-sm sm:text-base" style="color: var(--text-primary);">{{ $license->product_name }}</p>
                                @if($license->isCreditsBased())
                                    <span class="px-2 py-0.5 text-xs rounded-full bg-purple-500/20 text-purple-500">Credits</span>
                                @else
                                    <span class="px-2 py-0.5 text-xs rounded-full bg-blue-500/20 text-blue-500">Duration</span>
                                @endif
                            </div>
                            <p class="text-xs mt-1" style="color: var(--text-muted);">
                                @if($license->isCreditsBased())
                                    {{ $license->getCreditsRemaining() ?? '∞' }} credits remaining
                                @elseif($license->expires_at)
                                    Expires {{ $license->expires_at->format('d M Y') }}
                                @else
                                    Lifetime
                                @endif
                            </p>
                        </div>
                        <code class="text-xs font-mono px-2 py-1 rounded" style="background-color: var(--bg-card); color: var(--text-secondary);">{{ Str::limit($license->license_key, 15) }}</code>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <!-- My Products Widget -->
    @if($myProducts->count() > 0)
        <div class="card">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-base sm:text-lg font-bold" style="color: var(--text-primary);">My Products</h2>
                <a href="{{ route('products') }}" class="text-xs sm:text-sm font-medium text-primary-500 hover:text-primary-400" wire:navigate>View All →</a>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 sm:gap-4">
                @foreach($myProducts->take(3) as $order)
                    <div class="p-3 sm:p-4 rounded-xl flex items-center gap-3" style="background-color: var(--bg-hover);">
                        @if($order->product->thumbnail)
                            <img src="{{ asset($order->product->thumbnail) }}" class="w-12 h-12 sm:w-14 sm:h-14 rounded-lg object-cover flex-shrink-0">
                        @else
                            <div class="w-12 h-12 sm:w-14 sm:h-14 rounded-lg flex items-center justify-center flex-shrink-0" style="background-color: var(--bg-card);">
                                <svg class="w-6 h-6" style="color: var(--text-muted);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                                </svg>
                            </div>
                        @endif
                        <div class="flex-1 min-w-0">
                            <p class="font-medium text-sm truncate" style="color: var(--text-primary);">{{ $order->product->name }}</p>
                            <p class="text-xs" style="color: var(--text-muted);">v{{ $order->product->version }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <!-- New Products Widget -->
    @if($newProducts->count() > 0)
        <div class="card">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center gap-2">
                    <h2 class="text-base sm:text-lg font-bold" style="color: var(--text-primary);">New Products</h2>
                    <span class="px-2 py-0.5 text-xs font-medium rounded-full bg-gradient-to-r from-emerald-500 to-cyan-500 text-white">NEW</span>
                </div>
                <a href="{{ route('products') }}" class="text-xs sm:text-sm font-medium text-primary-500 hover:text-primary-400" wire:navigate>Browse All →</a>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3 sm:gap-4">
                @foreach($newProducts as $product)
                    <a href="{{ route('products') }}" class="group block p-3 rounded-xl transition-all hover:shadow-md" style="background-color: var(--bg-hover);" wire:navigate>
                        @if($product->thumbnail)
                            <img src="{{ asset($product->thumbnail) }}" class="w-full h-24 sm:h-28 rounded-lg object-cover mb-3 group-hover:scale-[1.02] transition-transform">
                        @else
                            <div class="w-full h-24 sm:h-28 rounded-lg flex items-center justify-center mb-3" style="background-color: var(--bg-card);">
                                <svg class="w-8 h-8" style="color: var(--text-muted);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                                </svg>
                            </div>
                        @endif
                        <p class="font-medium text-sm truncate" style="color: var(--text-primary);">{{ $product->name }}</p>
                        <div class="flex items-center justify-between mt-1">
                            @if($product->isFree())
                                <span class="text-xs font-medium text-emerald-500">FREE</span>
                            @else
                                <span class="text-xs font-medium text-primary-500">{{ $product->formatted_price }}</span>
                            @endif
                            <span class="text-xs" style="color: var(--text-muted);">v{{ $product->version }}</span>
                        </div>
                    </a>
                @endforeach
            </div>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 sm:gap-8">
        <!-- Recent Activity -->
        <div class="lg:col-span-2 space-y-4 sm:space-y-6">
            <div class="flex items-center justify-between">
                <h2 class="text-base sm:text-lg font-bold" style="color: var(--text-primary);">Recent Activity</h2>
                @if($recentGenerations->count() > 0)
                    <a href="{{ route('history') }}" class="text-xs sm:text-sm font-medium text-primary-500 hover:text-primary-600 transition-colors" wire:navigate>
                        View History →
                    </a>
                @endif
            </div>

            @if($recentGenerations->count() > 0)
                <div class="space-y-3 sm:space-y-4">
                    @foreach($recentGenerations as $generation)
                        <div class="group flex items-center gap-3 sm:gap-4 p-3 sm:p-4 rounded-2xl border transition-all duration-200 hover:shadow-md hover:border-primary-500/30" 
                             style="background-color: var(--bg-card); border-color: var(--border-color);">
                            @if($generation->image_path)
                                <img src="{{ Storage::url($generation->image_path) }}" alt="" class="w-12 h-12 sm:w-16 sm:h-16 rounded-xl object-cover shadow-sm group-hover:scale-105 transition-transform duration-300">
                            @else
                                <div class="w-12 h-12 sm:w-16 sm:h-16 rounded-xl flex items-center justify-center flex-shrink-0" style="background-color: var(--bg-hover);">
                                    <svg class="w-5 h-5 sm:w-6 sm:h-6" style="color: var(--text-muted);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                </div>
                            @endif
                            
                            <div class="flex-1 min-w-0">
                                <h3 class="font-semibold text-sm sm:text-base truncate pr-2" style="color: var(--text-primary);">{{ $generation->filename ?? 'Untitled Image' }}</h3>
                                <p class="text-xs sm:text-sm truncate pr-2 mt-0.5" style="color: var(--text-secondary);">{{ Str::limit($generation->title, 50) }}</p>
                            </div>
                            
                            <div class="text-right whitespace-nowrap hidden sm:block">
                                <span class="text-xs font-medium px-2.5 py-1 rounded-lg" style="background-color: var(--bg-hover); color: var(--text-secondary);">
                                    {{ $generation->created_at->diffForHumans(null, true, true) }}
                                </span>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-8 sm:py-12 rounded-3xl border border-dashed" style="border-color: var(--border-color);">
                    <div class="w-12 h-12 sm:w-16 sm:h-16 mx-auto rounded-2xl flex items-center justify-center mb-4" style="background-color: var(--bg-hover);">
                        <svg class="w-6 h-6 sm:w-8 sm:h-8" style="color: var(--text-muted);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <h3 class="font-bold text-base sm:text-lg mb-1" style="color: var(--text-primary);">No generations yet</h3>
                    <p class="text-xs sm:text-sm mb-4 sm:mb-6 max-w-xs mx-auto px-4" style="color: var(--text-secondary);">Start generating metadata for your images.</p>
                    <a href="{{ route('generate') }}" class="inline-flex items-center gap-2 px-4 sm:px-6 py-2 sm:py-2.5 rounded-xl bg-primary-600 hover:bg-primary-500 text-white text-sm font-medium transition-colors shadow-lg shadow-primary-500/20" wire:navigate>
                        <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        Start Generating
                    </a>
                </div>
            @endif
        </div>

        <!-- Quick Actions -->
        <div>
            <h2 class="text-base sm:text-lg font-bold mb-4 sm:mb-6" style="color: var(--text-primary);">Quick Actions</h2>
            <div class="space-y-3 sm:space-y-4">
                <a href="{{ route('generate') }}" class="block p-4 sm:p-5 rounded-2xl border transition-all duration-200 hover:border-primary-500 hover:shadow-lg group" 
                   style="background-color: var(--bg-card); border-color: var(--border-color);" wire:navigate>
                    <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-xl bg-gradient-to-br from-primary-500 to-accent-cyan flex items-center justify-center mb-3 sm:mb-4 shadow-lg shadow-primary-500/20 group-hover:scale-110 transition-transform">
                        <svg class="w-5 h-5 sm:w-6 sm:h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                    </div>
                    <h3 class="font-bold text-sm sm:text-lg mb-1" style="color: var(--text-primary);">New Generation</h3>
                    <p class="text-xs sm:text-sm" style="color: var(--text-secondary);">Upload images and generate metadata</p>
                </a>

                <a href="{{ route('licenses') }}" class="block p-4 sm:p-5 rounded-2xl border transition-all duration-200 hover:border-purple-500 hover:shadow-lg group" 
                   style="background-color: var(--bg-card); border-color: var(--border-color);" wire:navigate>
                    <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-xl bg-purple-500/10 flex items-center justify-center mb-3 sm:mb-4 group-hover:scale-110 transition-transform">
                        <svg class="w-5 h-5 sm:w-6 sm:h-6 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                        </svg>
                    </div>
                    <h3 class="font-bold text-sm sm:text-lg mb-1" style="color: var(--text-primary);">License Store</h3>
                    <p class="text-xs sm:text-sm" style="color: var(--text-secondary);">Purchase and manage licenses</p>
                </a>

                <a href="{{ route('products') }}" class="block p-4 sm:p-5 rounded-2xl border transition-all duration-200 hover:border-cyan-500 hover:shadow-lg group" 
                   style="background-color: var(--bg-card); border-color: var(--border-color);" wire:navigate>
                    <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-xl bg-cyan-500/10 flex items-center justify-center mb-3 sm:mb-4 group-hover:scale-110 transition-transform">
                        <svg class="w-5 h-5 sm:w-6 sm:h-6 text-cyan-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                        </svg>
                    </div>
                    <h3 class="font-bold text-sm sm:text-lg mb-1" style="color: var(--text-primary);">Product Store</h3>
                    <p class="text-xs sm:text-sm" style="color: var(--text-secondary);">Browse and download products</p>
                </a>

                <a href="{{ route('history') }}" class="block p-4 sm:p-5 rounded-2xl border transition-all duration-200 hover:border-primary-500 hover:shadow-lg group" 
                   style="background-color: var(--bg-card); border-color: var(--border-color);" wire:navigate>
                    <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-xl flex items-center justify-center mb-3 sm:mb-4 group-hover:scale-110 transition-transform" style="background-color: var(--bg-hover);">
                        <svg class="w-5 h-5 sm:w-6 sm:h-6" style="color: var(--text-primary);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <h3 class="font-bold text-sm sm:text-lg mb-1" style="color: var(--text-primary);">View History</h3>
                    <p class="text-xs sm:text-sm" style="color: var(--text-secondary);">Browse past generations</p>
                </a>
            </div>
        </div>
    </div>
</div>
