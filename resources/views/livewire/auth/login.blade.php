<div x-data>
    <div class="card animate-fade-in">
        <h2 class="text-2xl font-bold text-center mb-2" :class="$store.theme.isDark ? 'text-white' : 'text-surface-900'">Welcome back</h2>
        <p class="text-center mb-8" :class="$store.theme.isDark ? 'text-surface-200' : 'text-surface-500'">Sign in to your account</p>

        @if (session('error'))
            <div class="mb-6 p-4 bg-red-500/20 border border-red-500/30 rounded-xl text-red-400 text-sm">
                {{ session('error') }}
            </div>
        @endif

        <form wire:submit="login" class="space-y-5">
            <div>
                <label for="email" class="label">Email</label>
                <input 
                    type="email" 
                    id="email" 
                    wire:model="email" 
                    class="input" 
                    placeholder="you@example.com"
                    autofocus
                >
                @error('email')
                    <p class="mt-2 text-sm text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="password" class="label">Password</label>
                <input 
                    type="password" 
                    id="password" 
                    wire:model="password" 
                    class="input" 
                    placeholder="••••••••"
                >
                @error('password')
                    <p class="mt-2 text-sm text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex items-center justify-between">
                <label class="flex items-center gap-2 cursor-pointer">
                    <input 
                        type="checkbox" 
                        wire:model="remember" 
                        class="w-4 h-4 rounded text-primary-500 focus:ring-primary-500/50"
                        :class="$store.theme.isDark ? 'border-white/20 bg-surface-800' : 'border-surface-300 bg-white'"
                    >
                    <span class="text-sm" :class="$store.theme.isDark ? 'text-surface-200' : 'text-surface-500'">Remember me</span>
                </label>
            </div>

            <button type="submit" class="btn-primary w-full" wire:loading.attr="disabled">
                <span wire:loading.remove>Sign in</span>
                <span wire:loading class="flex items-center gap-2">
                    <span class="spinner"></span>
                    Signing in...
                </span>
            </button>
        </form>

        <!-- Divider -->
        <div class="relative my-6">
            <div class="absolute inset-0 flex items-center">
                <div class="w-full border-t" style="border-color: var(--border-color);"></div>
            </div>
            <div class="relative flex justify-center text-sm">
                <span class="px-3" style="background-color: var(--bg-card); color: var(--text-secondary);">Or continue with</span>
            </div>
        </div>

        <!-- Google Login Button -->
        <a 
            href="{{ route('auth.google') }}" 
            class="flex items-center justify-center gap-3 w-full px-4 py-3 rounded-xl border transition-all font-medium"
            style="background-color: var(--bg-card); border-color: var(--border-color); color: var(--text-primary);"
            onmouseover="this.style.backgroundColor='var(--bg-hover)'"
            onmouseout="this.style.backgroundColor='var(--bg-card)'"
        >
            <svg class="w-5 h-5" viewBox="0 0 24 24">
                <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
                <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
            </svg>
            Continue with Google
        </a>

        <p class="mt-6 text-center text-sm" :class="$store.theme.isDark ? 'text-surface-200' : 'text-surface-500'">
            Don't have an account? 
            <a href="{{ route('register') }}" class="text-primary-500 hover:text-primary-400 font-medium" wire:navigate>
                Sign up
            </a>
        </p>
    </div>
</div>
