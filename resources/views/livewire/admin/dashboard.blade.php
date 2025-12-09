<div class="space-y-8">
    <!-- Header -->
    <div>
        <h1 class="text-2xl lg:text-3xl font-bold" style="color: var(--text-primary);">Admin Dashboard</h1>
        <p class="mt-1" style="color: var(--text-secondary);">Monitor system statistics and recent activities</p>
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
                <div>
                    <p class="text-sm" style="color: var(--text-secondary);">Total Users</p>
                    <p class="text-2xl font-bold" style="color: var(--text-primary);">{{ number_format($totalUsers) }}</p>
                </div>
            </div>
        </div>

        <div class="card group hover:border-accent-emerald/30 transition-colors">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-accent-emerald/20 flex items-center justify-center group-hover:bg-accent-emerald/30 transition-colors">
                    <svg class="w-6 h-6 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-sm" style="color: var(--text-secondary);">Active Users</p>
                    <p class="text-2xl font-bold" style="color: var(--text-primary);">{{ number_format($activeUsers) }}</p>
                </div>
            </div>
        </div>

        <div class="card group hover:border-accent-cyan/30 transition-colors">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-accent-cyan/20 flex items-center justify-center group-hover:bg-accent-cyan/30 transition-colors">
                    <svg class="w-6 h-6 text-cyan-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-sm" style="color: var(--text-secondary);">Total Generations</p>
                    <p class="text-2xl font-bold" style="color: var(--text-primary);">{{ number_format($totalGenerations) }}</p>
                </div>
            </div>
        </div>

        <div class="card group hover:border-accent-amber/30 transition-colors">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-accent-amber/20 flex items-center justify-center group-hover:bg-accent-amber/30 transition-colors">
                    <svg class="w-6 h-6 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-sm" style="color: var(--text-secondary);">Active API Keys</p>
                    <p class="text-2xl font-bold" style="color: var(--text-primary);">{{ number_format($activeApiKeys) }}</p>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 lg:gap-8">
        <!-- Recent Users -->
        <div class="card">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-semibold" style="color: var(--text-primary);">Recent Users</h2>
                <a href="{{ route('admin.users') }}" class="text-primary-500 hover:text-primary-600 text-sm" wire:navigate>
                    View all →
                </a>
            </div>

            <div class="space-y-3">
                @forelse($recentUsers as $user)
                    <div class="flex items-center gap-4 p-3 rounded-xl" style="background-color: var(--bg-hover);">
                        <div class="w-10 h-10 rounded-full bg-gradient-to-br from-primary-500 to-accent-cyan flex items-center justify-center font-semibold text-sm text-white">
                            {{ strtoupper(substr($user->name, 0, 1)) }}
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="font-medium truncate" style="color: var(--text-primary);">{{ $user->name }}</p>
                            <p class="text-sm truncate" style="color: var(--text-secondary);">{{ $user->email }}</p>
                        </div>
                        <span class="px-2 py-1 rounded text-xs {{ $user->role === 'admin' ? 'bg-primary-500/20 text-primary-600' : '' }}" style="{{ $user->role !== 'admin' ? 'background-color: var(--bg-hover); color: var(--text-secondary);' : '' }}">
                            {{ ucfirst($user->role) }}
                        </span>
                    </div>
                @empty
                    <p class="text-center py-4" style="color: var(--text-secondary);">No users yet</p>
                @endforelse
            </div>
        </div>

        <!-- Recent Generations -->
        <div class="card">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-semibold" style="color: var(--text-primary);">Recent Generations</h2>
            </div>

            <div class="space-y-3">
                @forelse($recentGenerations as $generation)
                    <div class="flex items-center gap-4 p-3 rounded-xl" style="background-color: var(--bg-hover);">
                        @if($generation->image_path)
                            <img src="{{ Storage::url($generation->image_path) }}" alt="" class="w-10 h-10 rounded-lg object-cover">
                        @else
                            <div class="w-10 h-10 rounded-lg flex items-center justify-center" style="background-color: var(--bg-card); border: 1px solid var(--border-color);">
                                <svg class="w-5 h-5" style="color: var(--text-secondary);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                            </div>
                        @endif
                        <div class="flex-1 min-w-0">
                            <p class="font-medium truncate" style="color: var(--text-primary);">{{ $generation->filename ?? 'Untitled' }}</p>
                            <p class="text-sm truncate" style="color: var(--text-secondary);">by {{ $generation->user->name }}</p>
                        </div>
                        <span class="text-xs" style="color: var(--text-muted);">
                            {{ $generation->created_at->diffForHumans() }}
                        </span>
                    </div>
                @empty
                    <p class="text-center py-4" style="color: var(--text-secondary);">No generations yet</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
