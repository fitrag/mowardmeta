<div class="relative" x-data="{ 
    open: false,
    position: 'bottom',
    toggle() {
        if (!this.open) {
            const button = $el.getBoundingClientRect();
            const spaceBelow = window.innerHeight - button.bottom;
            this.position = spaceBelow < 220 ? 'top' : 'bottom';
        }
        this.open = !this.open;
    }
}">
    <button 
        @click="toggle()"
        class="p-1.5 rounded-md transition-colors"
        style="color: var(--text-muted);"
        onmouseover="this.style.backgroundColor='var(--bg-muted)'; this.style.color='var(--text-secondary)'"
        onmouseout="this.style.backgroundColor='transparent'; this.style.color='var(--text-muted)'"
        title="Change theme"
    >
        <svg x-show="$store.theme.current === 'light'" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/>
        </svg>
        <svg x-show="$store.theme.current === 'dark'" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/>
        </svg>
        <svg x-show="$store.theme.current === 'system'" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
        </svg>
    </button>

    <div 
        x-show="open" 
        @click.away="open = false"
        x-transition:enter="transition ease-out duration-100"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-75"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        :class="position === 'top' ? 'bottom-full mb-1 origin-bottom' : 'top-full mt-1 origin-top'"
        class="absolute right-0 w-36 rounded-lg shadow-lg overflow-hidden z-50"
        style="background-color: var(--bg-card); border: 1px solid var(--border-color);"
    >
        <button 
            wire:click="setTheme('light')"
            @click="$store.theme.set('light'); open = false"
            class="flex items-center gap-2.5 w-full px-3 py-2 text-left text-xs transition-colors"
            :class="$store.theme.current === 'light' ? 'text-[var(--accent)] bg-[var(--accent-muted)]' : 'text-[var(--text-secondary)]'"
            onmouseover="if (!$store.theme.current === 'light') this.style.backgroundColor='var(--bg-muted)'"
            onmouseout="if (!$store.theme.current === 'light') this.style.backgroundColor='transparent'"
        >
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/>
            </svg>
            Light
            <svg x-show="$store.theme.current === 'light'" class="w-3.5 h-3.5 ml-auto" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
            </svg>
        </button>
        
        <button 
            wire:click="setTheme('dark')"
            @click="$store.theme.set('dark'); open = false"
            class="flex items-center gap-2.5 w-full px-3 py-2 text-left text-xs transition-colors"
            :class="$store.theme.current === 'dark' ? 'text-[var(--accent)] bg-[var(--accent-muted)]' : 'text-[var(--text-secondary)]'"
        >
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/>
            </svg>
            Dark
            <svg x-show="$store.theme.current === 'dark'" class="w-3.5 h-3.5 ml-auto" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
            </svg>
        </button>
        
        <button 
            wire:click="setTheme('system')"
            @click="$store.theme.set('system'); open = false"
            class="flex items-center gap-2.5 w-full px-3 py-2 text-left text-xs transition-colors"
            :class="$store.theme.current === 'system' ? 'text-[var(--accent)] bg-[var(--accent-muted)]' : 'text-[var(--text-secondary)]'"
        >
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
            </svg>
            System
            <svg x-show="$store.theme.current === 'system'" class="w-3.5 h-3.5 ml-auto" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
            </svg>
        </button>
    </div>
</div>
