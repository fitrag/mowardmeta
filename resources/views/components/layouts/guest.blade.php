<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" 
      x-data 
      :class="$store.theme.isDark ? '' : 'light'"
      x-init="$store.theme.init('dark')"
>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? \App\Models\AppSetting::get('app_name', 'MetaGen') }} - {{ \App\Models\AppSetting::get('app_tagline', 'Microstock Metadata Generator') }}</title>

    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>🎨</text></svg>">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    
    <!-- Prevent flash of wrong theme -->
    <script>
        (function() {
            const theme = localStorage.getItem('theme') || 'dark';
            if (theme === 'light' || (theme === 'system' && window.matchMedia('(prefers-color-scheme: light)').matches)) {
                document.documentElement.classList.add('light');
            }
        })();
    </script>
</head>
<body class="min-h-screen font-sans antialiased" :class="$store.theme.isDark ? 'bg-surface-950 text-white' : 'bg-surface-50 text-surface-900'">
    <div class="min-h-screen flex flex-col items-center justify-center p-4 relative overflow-hidden">
        <!-- Background Effects -->
        <div class="absolute inset-0 overflow-hidden pointer-events-none">
            <div class="absolute -top-40 -right-40 w-80 h-80 rounded-full blur-3xl" :class="$store.theme.isDark ? 'bg-primary-500/20' : 'bg-primary-500/10'"></div>
            <div class="absolute -bottom-40 -left-40 w-80 h-80 rounded-full blur-3xl" :class="$store.theme.isDark ? 'bg-accent-cyan/20' : 'bg-accent-cyan/10'"></div>
        </div>

        <!-- Theme Toggle -->
        <div class="absolute top-4 right-4 z-20">
            <button 
                @click="$store.theme.set($store.theme.isDark ? 'light' : 'dark')"
                class="p-2 rounded-lg transition-colors"
                :class="$store.theme.isDark ? 'hover:bg-white/10 text-surface-200' : 'hover:bg-surface-200 text-surface-500'"
            >
                <!-- Sun icon for light theme -->
                <svg x-show="!$store.theme.isDark" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/>
                </svg>
                <!-- Moon icon for dark theme -->
                <svg x-show="$store.theme.isDark" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/>
                </svg>
            </button>
        </div>

        <!-- Logo -->
        <div class="flex items-center gap-3 mb-8 relative z-10">
            <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-primary-500 to-accent-cyan flex items-center justify-center">
                <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                </svg>
            </div>
            <div>
                <h1 class="font-bold text-2xl" :class="$store.theme.isDark ? 'text-white' : 'text-surface-900'">{{ \App\Models\AppSetting::get('app_name', 'MetaGen') }}</h1>
                <p class="text-sm" :class="$store.theme.isDark ? 'text-surface-200' : 'text-surface-500'">{{ \App\Models\AppSetting::get('app_tagline', 'Microstock Metadata Generator') }}</p>
            </div>
        </div>

        <!-- Content -->
        <div class="w-full max-w-md relative z-10">
            {{ $slot }}
        </div>

        <!-- Footer -->
        <p class="mt-8 text-sm relative z-10" :class="$store.theme.isDark ? 'text-surface-200/50' : 'text-surface-400'">
            &copy; {{ date('Y') }} {{ \App\Models\AppSetting::get('app_name', 'MetaGen') }}. Powered by AI.
        </p>
    </div>

    @livewireScripts
    
    <script>
        // Alpine.js Theme Store
        document.addEventListener('alpine:init', () => {
            Alpine.store('theme', {
                current: 'dark',
                isDark: true,
                
                init(defaultTheme) {
                    this.current = localStorage.getItem('theme') || defaultTheme || 'dark';
                    this.applyTheme();
                    
                    window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', () => {
                        if (this.current === 'system') {
                            this.applyTheme();
                        }
                    });
                },
                
                set(theme) {
                    this.current = theme;
                    localStorage.setItem('theme', theme);
                    this.applyTheme();
                },
                
                applyTheme() {
                    let isDark;
                    
                    if (this.current === 'system') {
                        isDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
                    } else {
                        isDark = this.current === 'dark';
                    }
                    
                    this.isDark = isDark;
                    
                    document.documentElement.classList.add('transitioning');
                    
                    if (isDark) {
                        document.documentElement.classList.remove('light');
                    } else {
                        document.documentElement.classList.add('light');
                    }
                    
                    setTimeout(() => {
                        document.documentElement.classList.remove('transitioning');
                    }, 300);
                }
            });
        });
    </script>
</body>
</html>
