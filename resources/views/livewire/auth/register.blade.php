<div>
    <div class="card animate-fade-in">
        <h2 class="text-2xl font-bold text-center mb-2">Create account</h2>
        <p class="text-surface-200 text-center mb-8">Start generating metadata for your stock images</p>

        <form wire:submit="register" class="space-y-5">
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
                    <p class="mt-2 text-sm text-red-400">{{ $message }}</p>
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

        <p class="mt-6 text-center text-sm text-surface-200">
            Already have an account? 
            <a href="{{ route('login') }}" class="text-primary-400 hover:text-primary-300 font-medium" wire:navigate>
                Sign in
            </a>
        </p>
    </div>
</div>
