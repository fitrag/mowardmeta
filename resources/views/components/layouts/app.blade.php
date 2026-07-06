<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? \App\Models\AppSetting::get('app_name', 'MetaGen') }} - {{ \App\Models\AppSetting::get('app_tagline', 'Microstock Metadata Generator') }}</title>

    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>🎨</text></svg>">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <script>
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
            
            window.__theme = localStorage.getItem('theme') || 'dark';
        })();
    </script>
</head>
<body class="min-h-screen font-sans antialiased" x-data x-init="$store.theme.init()">
    <div class="flex min-h-screen">
        <aside id="sidebar"
               class="fixed inset-y-0 left-0 z-50 flex flex-col -translate-x-full lg:translate-x-0"
               style="background-color: var(--bg-secondary); border-right: 1px solid var(--border-color); width: 256px; transition: transform 0.3s ease, width 0.3s ease;"
               x-data="{ collapsed: localStorage.getItem('sidebar-collapsed') === 'true' }"
               x-init="applyCollapsed(); $watch('collapsed', val => { localStorage.setItem('sidebar-collapsed', val); applyCollapsed(); })">
            <div class="flex flex-col h-full overflow-hidden">
                <div class="flex items-center gap-3 px-4 py-4 flex-shrink-0">
                    <div class="sidebar-logo w-8 h-8 rounded-lg flex items-center justify-center flex-shrink-0" style="background: linear-gradient(135deg, var(--accent), #06b6d4);">
                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                        </svg>
                    </div>
                    <div class="flex-1 min-w-0 sidebar-header-text">
                        <h1 class="font-semibold text-sm truncate" style="color: var(--text-primary);">{{ \App\Models\AppSetting::get('app_name', 'MetaGen') }}</h1>
                        <p class="text-[11px] truncate" style="color: var(--text-muted);">Metadata Generator</p>
                    </div>
                </div>

                <nav class="flex-1 px-2 overflow-y-auto overflow-x-hidden">
                    @auth
                        @if(auth()->user()->isAdmin())
                            <div class="px-3 mt-3 mb-1.5 sidebar-text">
                                <p class="text-[11px] font-semibold uppercase tracking-wider" style="color: var(--text-muted);">Admin</p>
                            </div>
                            
                            <a href="{{ route('admin.dashboard') }}" class="sidebar-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" wire:navigate>
                                <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
                                </svg>
                                <span class="sidebar-text">Dashboard</span>
                            </a>
                            
                            <a href="{{ route('admin.users') }}" class="sidebar-link {{ request()->routeIs('admin.users') ? 'active' : '' }}" wire:navigate>
                                <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                                </svg>
                                <span class="sidebar-text">Users</span>
                            </a>
                            
                            <a href="{{ route('admin.api-keys') }}" class="sidebar-link {{ request()->routeIs('admin.api-keys') ? 'active' : '' }}" wire:navigate>
                                <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                                </svg>
                                <span class="sidebar-text">API Keys</span>
                            </a>
                            
                            @php $pendingOrdersCount = \App\Models\SubscriptionOrder::pending()->count(); @endphp
                            <a href="{{ route('admin.orders') }}" class="sidebar-link {{ request()->routeIs('admin.orders') ? 'active' : '' }}" wire:navigate>
                                <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                </svg>
                                <span class="sidebar-text">Orders</span>
                                @if($pendingOrdersCount > 0)
                                    <span class="ml-auto px-1.5 py-0.5 text-[10px] font-medium rounded-md badge-warning sidebar-text">{{ $pendingOrdersCount }}</span>
                                @endif
                            </a>
                            
                            <a href="{{ route('admin.payment-methods') }}" class="sidebar-link {{ request()->routeIs('admin.payment-methods') ? 'active' : '' }}" wire:navigate>
                                <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                                </svg>
                                <span class="sidebar-text">Payments</span>
                            </a>
                            
                            <a href="{{ route('admin.subscription-plans') }}" class="sidebar-link {{ request()->routeIs('admin.subscription-plans') ? 'active' : '' }}" wire:navigate>
                                <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <span class="sidebar-text">Plans</span>
                            </a>
                            
                            <div class="px-4 mt-4 mb-2 sidebar-text">
                                <p class="text-xs font-bold uppercase tracking-wider opacity-50 sidebar-text" style="color: var(--text-muted);">License Management</p>
                                <span class="ml-1 px-1.5 py-0.5 text-[10px] font-bold rounded bg-gradient-to-r from-emerald-500 to-cyan-500 text-white sidebar-text">NEW</span>
                            </div>
                            
                            <a href="{{ route('admin.licenses') }}" class="sidebar-link {{ request()->routeIs('admin.licenses') ? 'active' : '' }}" wire:navigate>
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/>
                                </svg>
                                <span class="sidebar-text font-medium">Licenses</span>
                            </a>
                            
                            <a href="{{ route('admin.license-plans') }}" class="sidebar-link {{ request()->routeIs('admin.license-plans') ? 'active' : '' }}" wire:navigate>
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                                </svg>
                                <span class="sidebar-text font-medium">License Plans</span>
                            </a>
                            
                            @php $pendingLicenseOrders = \App\Models\LicenseOrder::pending()->count(); @endphp
                            <a href="{{ route('admin.license-orders') }}" class="sidebar-link {{ request()->routeIs('admin.license-orders') ? 'active' : '' }}" wire:navigate>
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                                </svg>
                                <span class="sidebar-text font-medium">License Orders</span>
                                @if($pendingLicenseOrders > 0)
                                    <span class="ml-auto px-2 py-0.5 text-xs rounded-full bg-amber-500/20 text-amber-500 sidebar-text">{{ $pendingLicenseOrders }}</span>
                                @endif
                            </a>
                            
                            <div class="px-4 mt-4 mb-2 sidebar-text">
                                <p class="text-xs font-bold uppercase tracking-wider opacity-50 sidebar-text" style="color: var(--text-muted);">Product Management</p>
                                <span class="ml-1 px-1.5 py-0.5 text-[10px] font-bold rounded bg-gradient-to-r from-emerald-500 to-cyan-500 text-white sidebar-text">NEW</span>
                            </div>
                            
                            <a href="{{ route('admin.products') }}" class="sidebar-link {{ request()->routeIs('admin.products') ? 'active' : '' }}" wire:navigate>
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                                </svg>
                                <span class="sidebar-text font-medium">Products</span>
                            </a>
                            
                            @php $pendingProductOrders = \App\Models\ProductOrder::pending()->count(); @endphp
                            <a href="{{ route('admin.product-orders') }}" class="sidebar-link {{ request()->routeIs('admin.product-orders') ? 'active' : '' }}" wire:navigate>
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                                </svg>
                                <span class="sidebar-text font-medium">Product Orders</span>
                                @if($pendingProductOrders > 0)
                                    <span class="ml-auto px-2 py-0.5 text-xs rounded-full bg-amber-500/20 text-amber-500 sidebar-text">{{ $pendingProductOrders }}</span>
                                @endif
                            </a>
                            
                            <a href="{{ route('admin.settings') }}" class="sidebar-link {{ request()->routeIs('admin.settings') ? 'active' : '' }}" wire:navigate>
                                <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                                <span class="sidebar-text">Settings</span>
                            </a>
                            
                            <div class="my-2 mx-2 sidebar-text" style="border-top: 1px solid var(--border-color);"></div>
                        @endif

                        <div class="px-3 mb-1.5 {{ auth()->user()->isAdmin() ? '' : 'mt-3' }} sidebar-text">
                            <p class="text-[11px] font-semibold uppercase tracking-wider" style="color: var(--text-muted);">Menu</p>
                        </div>

                        <a href="{{ route('dashboard') }}" class="sidebar-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" wire:navigate>
                            <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                            </svg>
                            <span class="sidebar-text">Dashboard</span>
                        </a>

                        <a href="{{ route('generate') }}" class="sidebar-link {{ request()->routeIs('generate') ? 'active' : '' }}" wire:navigate>
                            <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                            </svg>
                            <span class="sidebar-text">Generate</span>
                        </a>

                        <a href="{{ route('keywords') }}" class="sidebar-link {{ request()->routeIs('keywords') ? 'active' : '' }}" wire:navigate>
                            <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                            </svg>
                            <span class="sidebar-text">Keywords</span>
                        </a>

                        <a href="{{ route('history') }}" class="sidebar-link {{ request()->routeIs('history') ? 'active' : '' }}" wire:navigate>
                            <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <span class="sidebar-text">History</span>
                        </a>

                        <a href="{{ route('subscription') }}" class="sidebar-link {{ request()->routeIs('subscription') ? 'active' : '' }}" wire:navigate>
                            <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/>
                            </svg>
                            <span class="sidebar-text">Subscription</span>
                            @if(!auth()->user()->isSubscribed() && !auth()->user()->isAdmin())
                                <span class="ml-auto px-1.5 py-0.5 text-[10px] font-medium rounded-md badge-warning sidebar-text">Free</span>
                            @endif
                        </a>

                        <a href="{{ route('licenses') }}" class="sidebar-link {{ request()->routeIs('licenses') ? 'active' : '' }}" wire:navigate>
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/>
                            </svg>
                            <span class="sidebar-text font-medium">Licenses</span>
                            <span class="ml-auto px-1.5 py-0.5 text-[10px] font-bold rounded bg-gradient-to-r from-emerald-500 to-cyan-500 text-white sidebar-text">NEW</span>
                        </a>

                        <a href="{{ route('products') }}" class="sidebar-link {{ request()->routeIs('products') ? 'active' : '' }}" wire:navigate>
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                            </svg>
                            <span class="sidebar-text font-medium">Products</span>
                            <span class="ml-auto px-1.5 py-0.5 text-[10px] font-bold rounded bg-gradient-to-r from-emerald-500 to-cyan-500 text-white sidebar-text">NEW</span>
                        </a>

                        <a href="{{ route('settings') }}" class="sidebar-link {{ request()->routeIs('settings') ? 'active' : '' }}" wire:navigate>
                            <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                            <span class="sidebar-text">Settings</span>
                        </a>
                    @endauth
                </nav>

                @auth
                <div class="p-2 border-t flex-shrink-0" style="border-color: var(--border-color);">
                    <div class="sidebar-footer flex items-center gap-2 p-1.5 rounded-lg" style="background-color: var(--bg-muted);">
                        <div class="sidebar-avatar avatar-sm flex-shrink-0">
                            {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                        </div>
                        <div class="flex-1 min-w-0 sidebar-footer-text">
                            <div class="flex items-center gap-1.5">
                                <p class="text-xs font-medium truncate" style="color: var(--text-primary);">{{ auth()->user()->name }}</p>
                                @if(auth()->user()->isSubscribed())
                                    <span class="px-1 py-px text-[9px] font-bold rounded bg-gradient-to-r from-amber-500 to-orange-500 text-white">PRO</span>
                                @endif
                            </div>
                            <p class="text-[11px] truncate" style="color: var(--text-muted);">{{ auth()->user()->isAdmin() ? 'Admin' : 'User' }}</p>
                        </div>
                        
                        <div class="flex items-center gap-0.5 flex-shrink-0 sidebar-footer-actions">
                            <livewire:theme-switcher />
                            
                            <form action="{{ route('logout') }}" method="POST">
                                @csrf
                                <button type="submit" class="p-1.5 rounded-md transition-colors" style="color: var(--text-muted);" onmouseover="this.style.color='var(--danger)'; this.style.backgroundColor='var(--danger-muted)'" onmouseout="this.style.color='var(--text-muted)'; this.style.backgroundColor='transparent'" title="Logout">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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

        <button id="sidebar-toggle"
                @click="Alpine.$data(document.getElementById('sidebar')).collapsed = !Alpine.$data(document.getElementById('sidebar')).collapsed"
                class="hidden lg:block fixed top-4 z-50 p-1.5 rounded-lg transition-all duration-300"
                style="background-color: var(--bg-secondary); border: 1px solid var(--border-color); color: var(--text-muted); left: 244px;"
                onmouseover="this.style.backgroundColor='var(--bg-muted)'; this.style.color='var(--text-secondary)'"
                onmouseout="this.style.backgroundColor='var(--bg-secondary)'; this.style.color='var(--text-muted)'"
                title="Toggle sidebar">
            <svg class="w-4 h-4 transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7m8 14l-7-7 7-7"/>
            </svg>
        </button>

        <div id="sidebar-overlay" class="fixed inset-0 bg-black/50 z-40 hidden lg:hidden" onclick="toggleSidebar()"></div>

        <main id="main-content" class="flex-1 flex flex-col min-h-screen lg:ml-[256px] transition-all duration-300">
            <!-- Mobile Header -->
            <header class="sticky top-0 z-30 lg:hidden" 
                    style="background-color: var(--bg-secondary); border-bottom: 1px solid var(--border-color);">
                <div class="flex items-center justify-between px-4 py-3">
                    <div class="flex items-center gap-3">
                        <button onclick="toggleSidebar()" class="p-1.5 rounded-md transition-colors" style="color: var(--text-secondary);" onmouseover="this.style.backgroundColor='var(--bg-muted)'" onmouseout="this.style.backgroundColor='transparent'">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                            </svg>
                        </button>
                        <h1 class="font-semibold text-sm" style="color: var(--text-primary);">{{ \App\Models\AppSetting::get('app_name', 'MetaGen') }}</h1>
                    </div>
                    
                    <livewire:theme-switcher />
                </div>
            </header>

            <!-- Main Content Area -->
            <div class="flex-1 w-full p-4 lg:p-6">
                {{ $slot }}
            </div>
        </main>
    </div>

    @livewireScripts
    
    <script>
        function applyCollapsed() {
            const sidebar = document.getElementById('sidebar');
            const main = document.getElementById('main-content');
            const toggle = document.getElementById('sidebar-toggle');
            if (!sidebar) return;
            
            const alpine = Alpine.$data(sidebar);
            const collapsed = alpine.collapsed;
            
            if (collapsed) {
                sidebar.style.width = '64px';
                if (main && window.innerWidth >= 1024) {
                    main.style.marginLeft = '64px';
                }
                if (toggle) {
                    toggle.style.left = '52px';
                    toggle.querySelector('svg').style.transform = 'rotate(180deg)';
                }
                sidebar.querySelectorAll('.sidebar-link').forEach(link => {
                    link.style.justifyContent = 'center';
                    link.style.padding = '0.625rem';
                    link.style.gap = '0';
                });
                sidebar.querySelectorAll('.sidebar-link svg').forEach(svg => {
                    svg.style.margin = '0 auto';
                });
                
                const header = sidebar.querySelector('.flex.items-center.gap-3');
                if (header) {
                    header.style.justifyContent = 'center';
                    header.style.padding = '1rem 0';
                    header.style.gap = '0';
                }
            } else {
                sidebar.style.width = '256px';
                if (main && window.innerWidth >= 1024) {
                    main.style.marginLeft = '256px';
                }
                if (toggle) {
                    toggle.style.left = '244px';
                    toggle.querySelector('svg').style.transform = 'rotate(0deg)';
                }
                sidebar.querySelectorAll('.sidebar-link').forEach(link => {
                    link.style.justifyContent = 'flex-start';
                    link.style.padding = '0.625rem 0.875rem';
                    link.style.gap = '';
                });
                sidebar.querySelectorAll('.sidebar-link svg').forEach(svg => {
                    svg.style.margin = '';
                });
                
                const header = sidebar.querySelector('.flex.items-center.gap-3');
                if (header) {
                    header.style.justifyContent = 'flex-start';
                    header.style.padding = '1rem';
                    header.style.gap = '0.75rem';
                }
            }
            
            const navTexts = sidebar.querySelectorAll('nav .sidebar-text');
            navTexts.forEach(el => {
                el.style.display = collapsed ? 'none' : '';
            });
            
            const headerTexts = sidebar.querySelectorAll('.sidebar-header-text');
            headerTexts.forEach(el => {
                el.style.display = collapsed ? 'none' : '';
            });
            
            const logos = sidebar.querySelectorAll('.sidebar-logo');
            logos.forEach(el => {
                el.style.margin = collapsed ? '0 auto' : '0';
            });
            
            const header = sidebar.querySelector('.flex.items-center.gap-3');
            if (header) {
                if (collapsed) {
                    header.style.justifyContent = 'center';
                    header.style.paddingLeft = '0';
                    header.style.paddingRight = '0';
                    header.style.gap = '0';
                } else {
                    header.style.justifyContent = 'flex-start';
                    header.style.paddingLeft = '1rem';
                    header.style.paddingRight = '1rem';
                    header.style.gap = '0.75rem';
                }
            }
            
            const footerTexts = sidebar.querySelectorAll('.sidebar-footer-text, .sidebar-footer-actions');
            footerTexts.forEach(el => {
                el.style.display = collapsed ? 'none' : 'flex';
            });
            
            const footer = sidebar.querySelector('.sidebar-footer');
            if (footer) {
                if (collapsed) {
                    footer.style.justifyContent = 'center';
                } else {
                    footer.style.justifyContent = 'flex-start';
                }
            }
            
            const avatars = sidebar.querySelectorAll('.sidebar-avatar');
            avatars.forEach(el => {
                el.style.margin = collapsed ? '0' : '0';
            });
        }
        
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
                    this.current = localStorage.getItem('theme') || 'dark';
                    this.applyTheme();
                    
                    window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', () => {
                        if (this.current === 'system') {
                            this.applyTheme();
                        }
                    });
                    
                    setTimeout(applyCollapsed, 50);
                },
                
                set(theme) {
                    this.current = theme;
                    localStorage.setItem('theme', theme);
                    this.applyTheme();
                },
                
                applyTheme() {
                    const isDark = this.isDark;
                    
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
        
        document.addEventListener('livewire:navigated', () => {
            if (window.Alpine && Alpine.store('theme')) {
                Alpine.store('theme').applyTheme();
            } else {
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
            setTimeout(applyCollapsed, 50);
        });
        
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebar-overlay');
            if (!sidebar) return;
            
            const isHidden = sidebar.classList.contains('-translate-x-full');
            if (isHidden) {
                sidebar.classList.remove('-translate-x-full');
                if (overlay) overlay.classList.remove('hidden');
            } else {
                sidebar.classList.add('-translate-x-full');
                if (overlay) overlay.classList.add('hidden');
            }
        }
        
        if (typeof window.SwalTheme === 'undefined') {
        window.SwalTheme = {
            getColors() {
                const isLight = document.documentElement.classList.contains('light');
                return {
                    background: isLight ? '#ffffff' : '#18181b',
                    text: isLight ? '#18181b' : '#fafafa',
                    confirmButton: '#6366f1',
                    cancelButton: isLight ? '#71717a' : '#3f3f46',
                    border: isLight ? 'rgba(0,0,0,0.08)' : 'rgba(255,255,255,0.08)'
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
        
        window._Toast = Swal.mixin({
            toast: true,
            position: 'bottom-end',
            showConfirmButton: false,
            timer: 2500,
            timerProgressBar: true,
            background: document.documentElement.classList.contains('light') ? '#ffffff' : '#18181b',
            color: document.documentElement.classList.contains('light') ? '#18181b' : '#fafafa',
            customClass: {
                popup: 'swal-toast-popup',
            },
            didOpen: (toast) => {
                toast.addEventListener('mouseenter', Swal.stopTimer);
                toast.addEventListener('mouseleave', Swal.resumeTimer);
            }
        });
        
        window.showToast = function(message, type = 'success') {
            const isLight = document.documentElement.classList.contains('light');
            window._Toast.fire({
                icon: type,
                title: message,
                background: isLight ? '#ffffff' : '#18181b',
                color: isLight ? '#18181b' : '#fafafa',
            });
        };
        
        window.showConfirm = async function(options = {}) {
            const result = await window.SwalTheme.confirm(options);
            return result.isConfirmed;
        };
        }
    </script>
    
    <style>
        .swal-themed-popup {
            border-radius: 12px !important;
            border: 1px solid rgba(255,255,255,0.08) !important;
            box-shadow: 0 20px 40px -8px rgba(0, 0, 0, 0.4) !important;
        }
        .light .swal-themed-popup {
            border: 1px solid rgba(0,0,0,0.08) !important;
            box-shadow: 0 20px 40px -8px rgba(0, 0, 0, 0.12) !important;
        }
        .swal-toast-popup {
            border-radius: 10px !important;
            border: 1px solid rgba(255,255,255,0.08) !important;
            box-shadow: 0 8px 20px -4px rgba(0, 0, 0, 0.3) !important;
        }
        .light .swal-toast-popup {
            border: 1px solid rgba(0,0,0,0.08) !important;
            box-shadow: 0 8px 20px -4px rgba(0, 0, 0, 0.08) !important;
        }
        .swal-confirm-btn {
            background-color: #6366f1 !important;
            color: white !important;
            padding: 0.5rem 1.25rem !important;
            border-radius: 8px !important;
            font-weight: 500 !important;
            font-size: 0.8125rem !important;
            border: none !important;
            cursor: pointer !important;
            transition: all 0.15s !important;
            margin-left: 0.5rem !important;
        }
        .swal-confirm-btn:hover {
            opacity: 0.9 !important;
        }
        .swal-cancel-btn {
            background-color: var(--bg-muted) !important;
            color: var(--text-secondary) !important;
            padding: 0.5rem 1.25rem !important;
            border-radius: 8px !important;
            font-weight: 500 !important;
            font-size: 0.8125rem !important;
            border: 1px solid var(--border-color) !important;
            cursor: pointer !important;
            transition: all 0.15s !important;
        }
        .swal-cancel-btn:hover {
            background-color: var(--bg-card) !important;
        }
        .swal2-icon {
            border-color: rgba(99, 102, 241, 0.3) !important;
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
            background: linear-gradient(135deg, #6366f1, #06b6d4) !important;
        }
    </style>
</body>
</html>
