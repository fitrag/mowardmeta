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

        <p class="mt-6 text-center text-sm" :class="$store.theme.isDark ? 'text-surface-200' : 'text-surface-500'">
            Don't have an account? 
            <a href="{{ route('register') }}" class="text-primary-500 hover:text-primary-400 font-medium" wire:navigate>
                Sign up
            </a>
        </p>
    </div>
</div>
