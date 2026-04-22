<div>
    <div class="card animate-fade-in">
        <div class="mb-6">
            <h2 class="text-lg font-semibold" style="color: var(--text-primary);">Create account</h2>
            <p class="text-sm mt-0.5" style="color: var(--text-secondary);">Start generating metadata for your stock images</p>
        </div>

        <form wire:submit="register" class="space-y-4">
            <div>
                <label for="name" class="label">Name</label>
                <input 
                    type="text" 
                    id="name" 
                    wire:model="name" 
                    class="input" 
                    placeholder="John Doe"
                    autofocus
                >
                @error('name')
                    <p class="mt-1.5 text-xs" style="color: var(--danger);">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="email" class="label">Email</label>
                <input 
                    type="email" 
                    id="email" 
                    wire:model="email" 
                    class="input" 
                    placeholder="you@example.com"
                >
                @error('email')
                    <p class="mt-1.5 text-xs" style="color: var(--danger);">{{ $message }}</p>
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
                    <p class="mt-1.5 text-xs" style="color: var(--danger);">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="password_confirmation" class="label">Confirm Password</label>
                <input 
                    type="password" 
                    id="password_confirmation" 
                    wire:model="password_confirmation" 
                    class="input" 
                    placeholder="••••••••"
                >
            </div>

            <button type="submit" class="btn-primary w-full" wire:loading.attr="disabled">
                <span wire:loading.remove>Create account</span>
                <span wire:loading class="flex items-center gap-2">
                    <span class="spinner"></span>
                    Creating...
                </span>
            </button>
        </form>

        <p class="mt-5 text-center text-sm" style="color: var(--text-secondary);">
            Already have an account? 
            <a href="{{ route('login') }}" class="font-medium" style="color: var(--accent);" wire:navigate>
                Sign in
            </a>
        </p>
    </div>
</div>
