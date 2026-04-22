<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? \App\Models\AppSetting::get('app_name', 'MetaGen') }} - {{ \App\Models\AppSetting::get('app_tagline', 'Microstock Metadata Generator') }}</title>

    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>🎨</text></svg>">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    
    <script>
        (function() {
            const theme = localStorage.getItem('theme') || 'dark';
            if (theme === 'light' || (theme === 'system' && window.matchMedia('(prefers-color-scheme: light)').matches)) {
                document.documentElement.classList.add('light');
            }
        })();
    </script>
</head>
<body class="min-h-screen font-sans antialiased">
    <div class="min-h-screen flex flex-col items-center justify-center p-4 relative">
        <div class="absolute inset-0 overflow-hidden pointer-events-none">
            <div class="absolute top-1/4 -left-32 w-96 h-96 rounded-full blur-3xl opacity-20" style="background: var(--accent);"></div>
            <div class="absolute bottom-1/4 -right-32 w-96 h-96 rounded-full blur-3xl opacity-10" style="background: #06b6d4;"></div>
        </div>

        <div class="absolute top-4 right-4 z-20">
            <livewire:theme-switcher />
        </div>

        <div class="flex items-center gap-3 mb-8 relative z-10">
            <div class="w-10 h-10 rounded-xl flex items-center justify-center" style="background: linear-gradient(135deg, var(--accent), #06b6d4);">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                </svg>
            </div>
            <div>
                <h1 class="font-semibold text-xl" style="color: var(--text-primary);">{{ \App\Models\AppSetting::get('app_name', 'MetaGen') }}</h1>
                <p class="text-xs" style="color: var(--text-secondary);">{{ \App\Models\AppSetting::get('app_tagline', 'Microstock Metadata Generator') }}</p>
            </div>
        </div>

        <div class="w-full max-w-sm relative z-10">
            {{ $slot }}
        </div>

        <p class="mt-8 text-xs relative z-10" style="color: var(--text-muted);">
            &copy; {{ date('Y') }} {{ \App\Models\AppSetting::get('app_name', 'MetaGen') }}
        </p>
    </div>

    @livewireScripts
    
    <script>
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
                    }, 250);
                }
            });
        });
    </script>
</body>
</html>
