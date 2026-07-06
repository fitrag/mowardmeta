<div class="space-y-6">
    <!-- Header -->
    <div class="section-header">
        <h1>Admin Dashboard</h1>
        <p>Monitor system statistics and recent activities</p>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 lg:gap-6">
        <div class="card group hover:border-primary-500/30 transition-colors">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-primary-500/20 flex items-center justify-center group-hover:bg-primary-500/30 transition-colors">
                    <svg class="w-6 h-6 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"/>
                    </svg>
                </div>
            </div>
            <div class="mt-2 flex items-center gap-1.5">
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                <span class="text-[11px]" style="color: var(--text-muted);">{{ number_format($activeUsers) }} active</span>
            </div>
        </div>

        <div class="card p-4">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-[11px] font-medium uppercase tracking-wide" style="color: var(--text-muted);">Total Generations</p>
                    <p class="text-2xl font-bold mt-1" style="color: var(--text-primary);">{{ number_format($totalGenerations) }}</p>
                </div>
                <div class="w-8 h-8 rounded-lg flex items-center justify-center" style="background-color: var(--bg-muted);">
                    <svg class="w-4 h-4" style="color: var(--text-secondary);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                </div>
            </div>
            <div class="mt-2">
                <span class="text-[11px]" style="color: var(--text-muted);">All time</span>
            </div>
        </div>

        <div class="card p-4">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-[11px] font-medium uppercase tracking-wide" style="color: var(--text-muted);">API Keys</p>
                    <p class="text-2xl font-bold mt-1" style="color: var(--text-primary);">{{ number_format($activeApiKeys) }}</p>
                </div>
                <div class="w-8 h-8 rounded-lg flex items-center justify-center" style="background-color: var(--warning-muted);">
                    <svg class="w-4 h-4" style="color: var(--warning);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                    </svg>
                </div>
            </div>
            <div class="mt-2">
                <span class="text-[11px]" style="color: var(--text-muted);">Active providers</span>
            </div>
        </div>

        @php
            $pendingOrdersCount = \App\Models\SubscriptionOrder::pending()->count();
        @endphp
        <div class="card p-4">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-[11px] font-medium uppercase tracking-wide" style="color: var(--text-muted);">Pending Orders</p>
                    <p class="text-2xl font-bold mt-1" style="color: var(--text-primary);">{{ number_format($pendingOrdersCount) }}</p>
                </div>
                <div class="w-8 h-8 rounded-lg flex items-center justify-center" style="background-color: var(--danger-muted);">
                    <svg class="w-4 h-4" style="color: var(--danger);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                </div>
            </div>
            <div class="mt-2">
                <a href="{{ route('admin.orders') }}" class="text-[11px] font-medium" style="color: var(--accent);" wire:navigate>
                    Review orders &rarr;
                </a>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div>
        <h2 class="text-sm font-semibold mb-3" style="color: var(--text-primary);">Quick Access</h2>
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-3">
            <a href="{{ route('admin.users') }}" class="flex flex-col items-center gap-2 p-4 rounded-xl border transition-all hover:scale-[1.02]" style="background-color: var(--bg-card); border-color: var(--border-color);" wire:navigate>
                <div class="w-10 h-10 rounded-lg flex items-center justify-center" style="background-color: var(--accent-muted);">
                    <svg class="w-5 h-5" style="color: var(--accent);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                    </svg>
                </div>
                <span class="text-xs font-medium" style="color: var(--text-primary);">Users</span>
            </a>

            <a href="{{ route('admin.orders') }}" class="flex flex-col items-center gap-2 p-4 rounded-xl border transition-all hover:scale-[1.02]" style="background-color: var(--bg-card); border-color: var(--border-color);" wire:navigate>
                <div class="w-10 h-10 rounded-lg flex items-center justify-center" style="background-color: var(--warning-muted);">
                    <svg class="w-5 h-5" style="color: var(--warning);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                </div>
                <span class="text-xs font-medium" style="color: var(--text-primary);">Orders</span>
            </a>

            <a href="{{ route('admin.api-keys') }}" class="flex flex-col items-center gap-2 p-4 rounded-xl border transition-all hover:scale-[1.02]" style="background-color: var(--bg-card); border-color: var(--border-color);" wire:navigate>
                <div class="w-10 h-10 rounded-lg flex items-center justify-center" style="background-color: var(--success-muted);">
                    <svg class="w-5 h-5" style="color: var(--success);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                    </svg>
                </div>
                <span class="text-xs font-medium" style="color: var(--text-primary);">API Keys</span>
            </a>

            <a href="{{ route('admin.payment-methods') }}" class="flex flex-col items-center gap-2 p-4 rounded-xl border transition-all hover:scale-[1.02]" style="background-color: var(--bg-card); border-color: var(--border-color);" wire:navigate>
                <div class="w-10 h-10 rounded-lg flex items-center justify-center" style="background-color: var(--bg-muted);">
                    <svg class="w-5 h-5" style="color: var(--text-secondary);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                    </svg>
                </div>
                <span class="text-xs font-medium" style="color: var(--text-primary);">Payments</span>
            </a>

            <a href="{{ route('admin.subscription-plans') }}" class="flex flex-col items-center gap-2 p-4 rounded-xl border transition-all hover:scale-[1.02]" style="background-color: var(--bg-card); border-color: var(--border-color);" wire:navigate>
                <div class="w-10 h-10 rounded-lg flex items-center justify-center" style="background-color: var(--accent-muted);">
                    <svg class="w-5 h-5" style="color: var(--accent);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <span class="text-xs font-medium" style="color: var(--text-primary);">Plans</span>
            </a>

            <a href="{{ route('admin.settings') }}" class="flex flex-col items-center gap-2 p-4 rounded-xl border transition-all hover:scale-[1.02]" style="background-color: var(--bg-card); border-color: var(--border-color);" wire:navigate>
                <div class="w-10 h-10 rounded-lg flex items-center justify-center" style="background-color: var(--bg-muted);">
                    <svg class="w-5 h-5" style="color: var(--text-secondary);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                </div>
                <span class="text-xs font-medium" style="color: var(--text-primary);">Settings</span>
            </a>
        </div>

        <div class="card group hover:border-purple-500/30 transition-colors">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-purple-500/20 flex items-center justify-center group-hover:bg-purple-500/30 transition-colors">
                    <svg class="w-6 h-6 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-sm" style="color: var(--text-secondary);">Active Licenses</p>
                    <p class="text-2xl font-bold" style="color: var(--text-primary);">{{ number_format($activeLicenses) }}</p>
                </div>
            </div>
        </div>

        <a href="{{ route('admin.license-orders') }}" class="card group hover:border-orange-500/30 transition-colors" wire:navigate>
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-orange-500/20 flex items-center justify-center group-hover:bg-orange-500/30 transition-colors">
                    <svg class="w-6 h-6 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                </div>
                <div>
                    <p class="text-sm" style="color: var(--text-secondary);">Pending Orders</p>
                    <p class="text-2xl font-bold {{ $pendingLicenseOrders > 0 ? 'text-orange-500' : '' }}" style="{{ $pendingLicenseOrders == 0 ? 'color: var(--text-primary);' : '' }}">{{ number_format($pendingLicenseOrders) }}</p>
                </div>
            </div>
        </a>
    </div>

    <!-- Main Content Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
        <!-- Recent Users -->
        <div class="lg:col-span-2">
            <div class="card">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h2 class="text-sm font-semibold" style="color: var(--text-primary);">Recent Users</h2>
                        <p class="text-[11px] mt-0.5" style="color: var(--text-muted);">Newly registered accounts</p>
                    </div>
                    <a href="{{ route('admin.users') }}" class="btn-ghost text-xs" wire:navigate>
                        View All
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </a>
                </div>

                <div class="space-y-1.5">
                    @forelse($recentUsers as $user)
                        <div class="flex items-center gap-3 p-3 rounded-lg transition-colors" style="background-color: var(--bg-muted);" onmouseover="this.style.backgroundColor='var(--bg-card)'" onmouseout="this.style.backgroundColor='var(--bg-muted)'">
                            <div class="avatar-sm flex-shrink-0">
                                {{ strtoupper(substr($user->name, 0, 1)) }}
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium truncate" style="color: var(--text-primary);">{{ $user->name }}</p>
                                <p class="text-[11px] truncate" style="color: var(--text-muted);">{{ $user->email }}</p>
                            </div>
                            <div class="flex items-center gap-2 flex-shrink-0">
                                @if($user->is_active)
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                @else
                                    <span class="w-1.5 h-1.5 rounded-full bg-red-500"></span>
                                @endif
                                <span class="badge {{ $user->role === 'admin' ? 'badge-accent' : 'badge-neutral' }}">
                                    {{ ucfirst($user->role) }}
                                </span>
                            </div>
                        </div>
                    @empty
                        <div class="empty-state">
                            <div class="empty-state-icon">
                                <svg class="w-6 h-6" style="color: var(--text-muted);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                                </svg>
                            </div>
                            <h3>No users yet</h3>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Side Column -->
        <div class="space-y-4">
            <!-- Recent Generations -->
            <div class="card">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h2 class="text-sm font-semibold" style="color: var(--text-primary);">Recent Activity</h2>
                        <p class="text-[11px] mt-0.5" style="color: var(--text-muted);">Latest generations</p>
                    </div>
                </div>

                <div class="space-y-2">
                    @forelse($recentGenerations as $generation)
                        <div class="flex items-center gap-3 p-2.5 rounded-lg" style="background-color: var(--bg-muted);">
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
                                <p class="text-[11px] truncate" style="color: var(--text-muted);">{{ $generation->user->name }}</p>
                            </div>
                            <span class="text-[11px] flex-shrink-0" style="color: var(--text-muted);">
                                {{ $generation->created_at->diffForHumans(null, true, true) }}
                            </span>
                        </div>
                    @empty
                        <p class="text-center py-4 text-xs" style="color: var(--text-muted);">No generations yet</p>
                    @endforelse
                </div>
            </div>

            <!-- System Status -->
            <div class="card" style="border-color: var(--success-muted);">
                <div class="flex items-center gap-2 mb-3">
                    <div class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></div>
                    <h2 class="text-sm font-semibold" style="color: var(--text-primary);">System Status</h2>
                </div>
                <div class="space-y-2">
                    <div class="flex items-center justify-between text-xs">
                        <span style="color: var(--text-secondary);">AI Provider</span>
                        <span class="font-medium" style="color: var(--success);">{{ ucfirst(\App\Models\AppSetting::get('ai_provider', 'gemini')) }}</span>
                    </div>
                    <div class="flex items-center justify-between text-xs">
                        <span style="color: var(--text-secondary);">Daily Limit</span>
                        <span class="font-medium" style="color: var(--text-primary);">{{ \App\Models\AppSetting::get('free_user_daily_limit', 10) }}</span>
                    </div>
                    <div class="flex items-center justify-between text-xs">
                        <span style="color: var(--text-secondary);">Storage</span>
                        <span class="font-medium" style="color: var(--text-primary);">Local</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
