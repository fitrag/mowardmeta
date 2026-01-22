<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? \App\Models\AppSetting::get('app_name', 'MetaGen') }} - {{ \App\Models\AppSetting::get('app_tagline', 'Microstock Metadata Generator') }}</title>

    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>🎨</text></svg>">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <!-- Theme initialization - runs before anything else -->
    <script>
        // Immediately apply theme from localStorage to prevent flash
        (function() {
            function getEffectiveTheme() {
                const stored = localStorage.getItem('theme') || 'dark';
                if (stored === 'system') {
                    return window.matchMedia('(prefers-color-scheme: light)').matches ? 'light' : 'dark';
                }
                return stored;
            }
            
            const theme = getEffectiveTheme();
            if (theme === 'light') {
                document.documentElement.classList.add('light');
            } else {
                document.documentElement.classList.remove('light');
            }
            
            // Store for Alpine to read
            window.__theme = localStorage.getItem('theme') || 'dark';
        })();
    </script>
</head>
<body class="min-h-screen font-sans antialiased" x-data x-init="$store.theme.init()">
    <div class="flex min-h-screen">
        <!-- Sidebar -->
        <aside id="sidebar" class="fixed inset-y-0 left-0 z-50 w-72 backdrop-blur-xl transform transition-transform duration-300 ease-in-out lg:translate-x-0 -translate-x-full border-r"
               style="background-color: var(--bg-secondary); border-color: var(--border-color);">
            <div class="flex flex-col h-full">
                <!-- Logo -->
                <div class="flex items-center gap-3 px-8 py-8">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-primary-500 to-accent-cyan flex items-center justify-center shadow-lg shadow-primary-500/20">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                        </svg>
                    </div>
                    <div>
                        <h1 class="font-bold text-xl tracking-tight" style="color: var(--text-primary);">{{ \App\Models\AppSetting::get('app_name', 'MetaGen') }}</h1>
                        <p class="text-xs font-medium" style="color: var(--text-secondary);">{{ \App\Models\AppSetting::get('app_tagline', 'Metadata Generator') }}</p>
                    </div>
                </div>

                <!-- Navigation -->
                <nav class="flex-1 px-4 space-y-1 overflow-y-auto custom-scrollbar">
                    @auth
                        @if(auth()->user()->isAdmin())
                            <div class="px-4 mt-2 mb-2">
                                <p class="text-xs font-bold uppercase tracking-wider opacity-50" style="color: var(--text-muted);">Administration</p>
                            </div>
                            
                            <a href="{{ route('admin.dashboard') }}" class="sidebar-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" wire:navigate>
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
                                </svg>
                                <span class="font-medium">Dashboard</span>
                            </a>
                            
                            <a href="{{ route('admin.users') }}" class="sidebar-link {{ request()->routeIs('admin.users') ? 'active' : '' }}" wire:navigate>
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                                </svg>
                                <span class="font-medium">Users</span>
                            </a>
                            
                            <a href="{{ route('admin.api-keys') }}" class="sidebar-link {{ request()->routeIs('admin.api-keys') ? 'active' : '' }}" wire:navigate>
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                                </svg>
                                <span class="font-medium">API Keys</span>
                            </a>
                            
                            @php $pendingOrdersCount = \App\Models\SubscriptionOrder::pending()->count(); @endphp
                            <a href="{{ route('admin.orders') }}" class="sidebar-link {{ request()->routeIs('admin.orders') ? 'active' : '' }}" wire:navigate>
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                </svg>
                                <span class="font-medium">Orders</span>
                                @if($pendingOrdersCount > 0)
                                    <span class="ml-auto px-2 py-0.5 text-xs rounded-full bg-amber-500/20 text-amber-500">{{ $pendingOrdersCount }}</span>
                                @endif
                            </a>
                            
                            <a href="{{ route('admin.payment-methods') }}" class="sidebar-link {{ request()->routeIs('admin.payment-methods') ? 'active' : '' }}" wire:navigate>
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                                </svg>
                                <span class="font-medium">Payment Methods</span>
                            </a>
                            
                            <a href="{{ route('admin.subscription-plans') }}" class="sidebar-link {{ request()->routeIs('admin.subscription-plans') ? 'active' : '' }}" wire:navigate>
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <span class="font-medium">Subscription Plans</span>
                            </a>
                            
                            <a href="{{ route('admin.settings') }}" class="sidebar-link {{ request()->routeIs('admin.settings') ? 'active' : '' }}" wire:navigate>
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                                <span class="font-medium">App Settings</span>
                            </a>
                            
                            <div class="my-6 border-t mx-4 opacity-50" style="border-color: var(--border-color);"></div>
                        @endif

                        <div class="px-4 mb-2 {{ auth()->user()->isAdmin() ? '' : 'mt-2' }}">
                            <p class="text-xs font-bold uppercase tracking-wider opacity-50" style="color: var(--text-muted);">Menu</p>
                        </div>

                        <a href="{{ route('dashboard') }}" class="sidebar-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" wire:navigate>
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                            </svg>
                            <span class="font-medium">Dashboard</span>
                        </a>

                        <a href="{{ route('generate') }}" class="sidebar-link {{ request()->routeIs('generate') ? 'active' : '' }}" wire:navigate>
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                            </svg>
                            <span class="font-medium">Generate Metadata</span>
                        </a>

                        <a href="{{ route('keywords') }}" class="sidebar-link {{ request()->routeIs('keywords') ? 'active' : '' }}" wire:navigate>
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                            </svg>
                            <span class="font-medium">Keyword Generator</span>
                        </a>

                        <a href="{{ route('history') }}" class="sidebar-link {{ request()->routeIs('history') ? 'active' : '' }}" wire:navigate>
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <span class="font-medium">History</span>
                        </a>

                        <a href="{{ route('subscription') }}" class="sidebar-link {{ request()->routeIs('subscription') ? 'active' : '' }}" wire:navigate>
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/>
                            </svg>
                            <span class="font-medium">Subscription</span>
                            @if(!auth()->user()->isSubscribed() && !auth()->user()->isAdmin())
                                <span class="ml-auto px-2 py-0.5 text-xs rounded-full bg-amber-500/20 text-amber-500">Free</span>
                            @endif
                        </a>

                        <a href="{{ route('settings') }}" class="sidebar-link {{ request()->routeIs('settings') ? 'active' : '' }}" wire:navigate>
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                            <span class="font-medium">Settings</span>
                        </a>
                    @endauth
                </nav>

                <!-- User Menu -->
                @auth
                <div class="p-4 border-t" style="border-color: var(--border-color);">
                    <div class="flex items-center gap-3 p-3 rounded-2xl transition-colors group relative" style="background-color: transparent;" onmouseover="this.style.backgroundColor='var(--bg-hover)'" onmouseout="this.style.backgroundColor='transparent'">
                        <div class="w-10 h-10 rounded-full bg-gradient-to-br from-primary-500 to-accent-cyan flex items-center justify-center font-bold text-white shadow-md">
                            {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2">
                                <p class="font-semibold text-sm truncate" style="color: var(--text-primary);">{{ auth()->user()->name }}</p>
                                @if(auth()->user()->isSubscribed())
                                    <span class="px-1.5 py-0.5 text-[10px] font-bold rounded bg-gradient-to-r from-amber-500 to-orange-500 text-white">PRO</span>
                                @endif
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                                <p class="text-xs truncate opacity-70" style="color: var(--text-secondary);">{{ auth()->user()->isAdmin() ? 'Administrator' : 'Online' }}</p>
                            </div>
                        </div>
                        
                        <!-- Settings / Logout Dropdown Trigger (Simplified for now) -->
                        <div class="flex items-center gap-1">
                            <livewire:theme-switcher />
                            
                            <form action="{{ route('logout') }}" method="POST">
                                @csrf
                                <button type="submit" class="p-2 rounded-lg hover:text-red-500 hover:bg-red-500/10 transition-all" style="color: var(--text-muted);" title="Logout">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                                    </svg>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
                @endauth
            </div>
        </aside>

        <!-- Sidebar Overlay (Mobile) -->
        <div id="sidebar-overlay" class="fixed inset-0 bg-black/50 z-40 lg:hidden hidden" onclick="toggleSidebar()"></div>

        <!-- Main Content -->
        <main class="flex-1 flex flex-col min-h-screen lg:ml-72 transition-all duration-300">
            <!-- Top Header (Mobile) -->
            <header class="sticky top-0 z-30 backdrop-blur-xl lg:hidden" 
                    style="background-color: var(--bg-secondary); border-bottom: 1px solid var(--border-color);">
                <div class="flex items-center justify-between px-4 py-3">
                    <div class="flex items-center gap-4">
                        <button onclick="toggleSidebar()" class="p-2 rounded-lg transition-colors" style="color: var(--text-secondary);">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                            </svg>
                        </button>
                        <h1 class="font-bold text-lg" style="color: var(--text-primary);">{{ \App\Models\AppSetting::get('app_name', 'MetaGen') }}</h1>
                    </div>
                    
                    <!-- Theme Switcher Mobile -->
                    <livewire:theme-switcher />
                </div>
            </header>

            <!-- Page Content -->
            <div class="flex-1 p-4 lg:p-8">
                {{ $slot }}
            </div>
        </main>
    </div>

    @livewireScripts
    
    <script>
        // Alpine.js Theme Store - persists in localStorage only
        document.addEventListener('alpine:init', () => {
            Alpine.store('theme', {
                current: window.__theme || 'dark',
                
                get isDark() {
                    if (this.current === 'system') {
                        return window.matchMedia('(prefers-color-scheme: dark)').matches;
                    }
                    return this.current === 'dark';
                },
                
                init() {
                    // Read from localStorage
                    this.current = localStorage.getItem('theme') || 'dark';
                    this.applyTheme();
                    
                    // Listen for system theme changes
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
                    const isDark = this.isDark;
                    
                    // Add transition class
                    document.documentElement.classList.add('transitioning');
                    
                    if (isDark) {
                        document.documentElement.classList.remove('light');
                    } else {
                        document.documentElement.classList.add('light');
                    }
                    
                    // Remove transition class after animation
                    setTimeout(() => {
                        document.documentElement.classList.remove('transitioning');
                    }, 300);
                }
            });
        });
        
        // Re-apply theme on Livewire navigate (this is the key fix!)
        document.addEventListener('livewire:navigated', () => {
            if (window.Alpine && Alpine.store('theme')) {
                Alpine.store('theme').applyTheme();
            } else {
                // Fallback: apply directly from localStorage
                const stored = localStorage.getItem('theme') || 'dark';
                let isDark = stored === 'dark';
                if (stored === 'system') {
                    isDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
                }
                if (isDark) {
                    document.documentElement.classList.remove('light');
                } else {
                    document.documentElement.classList.add('light');
                }
            }
        });
        
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebar-overlay');
            
            sidebar.classList.toggle('-translate-x-full');
            overlay.classList.toggle('hidden');
        }
        
        // SweetAlert2 Custom Theme Configuration - Only declare once
        if (typeof window.SwalTheme === 'undefined') {
        window.SwalTheme = {
            getColors() {
                const isLight = document.documentElement.classList.contains('light');
                return {
                    background: isLight ? '#ffffff' : '#1a1b23',
                    text: isLight ? '#1f2937' : '#ffffff',
                    confirmButton: '#8b5cf6',
                    cancelButton: isLight ? '#6b7280' : '#374151',
                    border: isLight ? 'rgba(0,0,0,0.1)' : 'rgba(255,255,255,0.1)'
                };
            },
            
            confirm(options = {}) {
                const colors = this.getColors();
                return Swal.fire({
                    title: options.title || 'Are you sure?',
                    text: options.text || '',
                    icon: options.icon || 'warning',
                    showCancelButton: true,
                    confirmButtonText: options.confirmText || 'Yes, proceed',
                    cancelButtonText: options.cancelText || 'Cancel',
                    reverseButtons: true,
                    background: colors.background,
                    color: colors.text,
                    customClass: {
                        popup: 'swal-themed-popup',
                        confirmButton: 'swal-confirm-btn',
                        cancelButton: 'swal-cancel-btn',
                    },
                    buttonsStyling: false,
                    ...options
                });
            },
            
            success(title, text = '') {
                const colors = this.getColors();
                return Swal.fire({
                    title: title,
                    text: text,
                    icon: 'success',
                    timer: 2000,
                    showConfirmButton: false,
                    background: colors.background,
                    color: colors.text,
                    customClass: {
                        popup: 'swal-themed-popup',
                    }
                });
            },
            
            error(title, text = '') {
                const colors = this.getColors();
                return Swal.fire({
                    title: title,
                    text: text,
                    icon: 'error',
                    background: colors.background,
                    color: colors.text,
                    customClass: {
                        popup: 'swal-themed-popup',
                        confirmButton: 'swal-confirm-btn',
                    },
                    buttonsStyling: false,
                });
            }
        };
        
        // Toast Configuration
        window._Toast = Swal.mixin({
            toast: true,
            position: 'bottom-end',
            showConfirmButton: false,
            timer: 2500,
            timerProgressBar: true,
            background: document.documentElement.classList.contains('light') ? '#ffffff' : '#1a1b23',
            color: document.documentElement.classList.contains('light') ? '#1f2937' : '#ffffff',
            customClass: {
                popup: 'swal-toast-popup',
            },
            didOpen: (toast) => {
                toast.addEventListener('mouseenter', Swal.stopTimer);
                toast.addEventListener('mouseleave', Swal.resumeTimer);
            }
        });
        
        // Global toast function
        window.showToast = function(message, type = 'success') {
            const isLight = document.documentElement.classList.contains('light');
            window._Toast.fire({
                icon: type,
                title: message,
                background: isLight ? '#ffffff' : '#1a1b23',
                color: isLight ? '#1f2937' : '#ffffff',
            });
        };
        
        // Global confirm function
        window.showConfirm = async function(options = {}) {
            const result = await window.SwalTheme.confirm(options);
            return result.isConfirmed;
        };
        } // End of if (typeof window.SwalTheme === 'undefined')
    </script>
    
    <style>
        .swal-themed-popup {
            border-radius: 1rem !important;
            border: 1px solid rgba(255,255,255,0.1) !important;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5) !important;
        }
        .light .swal-themed-popup {
            border: 1px solid rgba(0,0,0,0.1) !important;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.15) !important;
        }
        .swal-toast-popup {
            border-radius: 0.75rem !important;
            border: 1px solid rgba(255,255,255,0.1) !important;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.3) !important;
        }
        .light .swal-toast-popup {
            border: 1px solid rgba(0,0,0,0.1) !important;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1) !important;
        }
        .swal-confirm-btn {
            background: linear-gradient(135deg, #8b5cf6, #06b6d4) !important;
            color: white !important;
            padding: 0.625rem 1.5rem !important;
            border-radius: 0.75rem !important;
            font-weight: 500 !important;
            font-size: 0.875rem !important;
            border: none !important;
            cursor: pointer !important;
            transition: all 0.2s !important;
            margin-left: 0.5rem !important;
        }
        .swal-confirm-btn:hover {
            opacity: 0.9 !important;
            transform: translateY(-1px) !important;
        }
        .swal-cancel-btn {
            background: rgba(107, 114, 128, 0.2) !important;
            color: #9ca3af !important;
            padding: 0.625rem 1.5rem !important;
            border-radius: 0.75rem !important;
            font-weight: 500 !important;
            font-size: 0.875rem !important;
            border: 1px solid rgba(107, 114, 128, 0.3) !important;
            cursor: pointer !important;
            transition: all 0.2s !important;
        }
        .swal-cancel-btn:hover {
            background: rgba(107, 114, 128, 0.3) !important;
        }
        .swal2-icon {
            border-color: rgba(139, 92, 246, 0.3) !important;
        }
        .swal2-icon.swal2-success {
            border-color: rgba(16, 185, 129, 0.3) !important;
        }
        .swal2-icon.swal2-success .swal2-success-ring {
            border-color: rgba(16, 185, 129, 0.3) !important;
        }
        .swal2-icon.swal2-success [class^=swal2-success-line] {
            background-color: #10b981 !important;
        }
        .swal2-icon.swal2-warning {
            border-color: rgba(245, 158, 11, 0.3) !important;
            color: #f59e0b !important;
        }
        .swal2-icon.swal2-error {
            border-color: rgba(239, 68, 68, 0.3) !important;
        }
        .swal2-icon.swal2-error [class^=swal2-x-mark-line] {
            background-color: #ef4444 !important;
        }
        .swal2-timer-progress-bar {
            background: linear-gradient(135deg, #8b5cf6, #06b6d4) !important;
        }
    </style>
</body>
</html>
