<div class="space-y-6">
    <!-- Header -->
    <div class="section-header">
        <h1>Subscription</h1>
        <p>Upgrade your account to unlock unlimited generations</p>
    </div>

    <!-- Alerts -->
    @if(session('success'))
        <div class="p-3 rounded-lg flex items-center gap-2 text-sm" style="background-color: var(--success-muted);">
            <svg class="w-4 h-4 flex-shrink-0" style="color: var(--success);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <span style="color: var(--success);">{{ session('success') }}</span>
        </div>
    @endif

    @if(session('error'))
        <div class="p-3 rounded-lg flex items-center gap-2 text-sm" style="background-color: var(--danger-muted);">
            <svg class="w-4 h-4 flex-shrink-0" style="color: var(--danger);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <span style="color: var(--danger);">{{ session('error') }}</span>
        </div>
    @endif

    @if(session('info'))
        <div class="p-3 rounded-lg flex items-center gap-2 text-sm" style="background-color: var(--accent-muted);">
            <svg class="w-4 h-4 flex-shrink-0" style="color: var(--accent);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <span style="color: var(--accent);">{{ session('info') }}</span>
        </div>
    @endif

    <!-- Current Status Card -->
    <div class="card">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div class="flex items-center gap-4">
                @if($isSubscribed)
                    <div class="w-12 h-12 rounded-xl flex items-center justify-center" style="background-color: var(--success-muted);">
                        <svg class="w-6 h-6" style="color: var(--success);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                @else
                    <div class="w-12 h-12 rounded-xl flex items-center justify-center" style="background-color: var(--warning-muted);">
                        <svg class="w-6 h-6" style="color: var(--warning);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                @endif
                <div>
                    <h2 class="text-sm font-semibold" style="color: var(--text-primary);">
                        {{ $isSubscribed ? 'Active Subscription' : 'Free Account' }}
                    </h2>
                    @if($isSubscribed)
                        @if($user->subscription_expires_at)
                            <p class="text-xs mt-0.5" style="color: var(--text-secondary);">
                                Valid until <strong style="color: var(--text-primary);">{{ $user->subscription_expires_at->format('d M Y') }}</strong>
                                <span class="ml-1" style="color: var(--text-muted);">({{ $user->subscription_expires_at->diffForHumans() }})</span>
                            </p>
                        @else
                            <p class="text-xs mt-0.5" style="color: var(--text-secondary);">Lifetime subscription</p>
                        @endif
                    @else
                        <p class="text-xs mt-0.5" style="color: var(--text-secondary);">
                            Limited to <strong style="color: var(--text-primary);">{{ $dailyLimit }}</strong> generations per day
                        </p>
                    @endif
                </div>
            </div>

            @if(!$isSubscribed)
                <div class="flex items-center gap-3 p-3 rounded-xl" style="background-color: var(--bg-muted);">
                    <div class="text-center">
                        <p class="text-[11px]" style="color: var(--text-muted);">Today's Usage</p>
                        <p class="text-xl font-bold" style="color: var(--text-primary);">{{ $todayCount }}<span class="text-sm font-normal" style="color: var(--text-muted);">/{{ $dailyLimit }}</span></p>
                    </div>
                    <div class="w-px h-8" style="background-color: var(--border-color);"></div>
                    <div class="text-center">
                        <p class="text-[11px]" style="color: var(--text-muted);">Remaining</p>
                        <p class="text-xl font-bold {{ $remainingGenerations > 0 ? 'text-emerald-500' : 'text-red-500' }}">{{ $remainingGenerations }}</p>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- Plans -->
    @if(!$isSubscribed && $plans->count() > 0)
        <div>
            <h2 class="text-sm font-semibold mb-3" style="color: var(--text-primary);">Choose a Plan</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($plans as $plan)
                    <div class="card p-0 overflow-hidden transition-all hover:border-[var(--border-color-strong)] {{ $loop->first ? 'ring-1 ring-[var(--accent)]' : '' }}">
                        <div class="p-5">
                            <div class="flex items-center justify-between mb-3">
                                <h3 class="text-base font-semibold" style="color: var(--text-primary);">{{ $plan->name }}</h3>
                                @if($loop->first)
                                    <span class="badge badge-accent">Recommended</span>
                                @endif
                            </div>
                            <p class="text-xs" style="color: var(--text-muted);">{{ $plan->duration_label }}</p>
                            
                            <div class="mt-3 flex items-baseline gap-1">
                                <span class="text-2xl font-bold" style="color: var(--accent);">{{ $plan->formatted_price }}</span>
                                <span class="text-xs" style="color: var(--text-muted);">/ {{ $plan->duration_days }} days</span>
                            </div>
                            
                            @if($plan->description)
                                <p class="text-xs mt-2 leading-relaxed" style="color: var(--text-secondary);">{{ $plan->description }}</p>
                            @endif
                        </div>
                        
                        <div class="px-5 pb-4">
                            <div class="space-y-2 mb-4">
                                <div class="flex items-center gap-2 text-xs" style="color: var(--text-secondary);">
                                    <svg class="w-4 h-4" style="color: var(--success);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                    </svg>
                                    Unlimited generations
                                </div>
                                <div class="flex items-center gap-2 text-xs" style="color: var(--text-secondary);">
                                    <svg class="w-4 h-4" style="color: var(--success);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                    </svg>
                                    Full AI image analysis
                                </div>
                                <div class="flex items-center gap-2 text-xs" style="color: var(--text-secondary);">
                                    <svg class="w-4 h-4" style="color: var(--success);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                    </svg>
                                    Priority support
                                </div>
                            </div>
                            
                            <button
                                wire:click="selectPlan({{ $plan->id }})"
                                class="btn-primary w-full justify-center"
                            >
                                Subscribe Now
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <!-- Subscribed CTA -->
    @if($isSubscribed)
        <div class="card text-center py-12">
            <div class="w-16 h-16 rounded-2xl flex items-center justify-center mx-auto mb-4" style="background: linear-gradient(135deg, var(--success-muted), rgba(16,185,129,0.2));">
                <svg class="w-8 h-8" style="color: var(--success);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <h3 class="text-lg font-semibold mb-1" style="color: var(--text-primary);">You're Subscribed!</h3>
            <p class="text-sm max-w-sm mx-auto" style="color: var(--text-secondary);">
                Enjoy unlimited metadata generations with full AI image analysis. Thank you for your support!
            </p>
            <a href="{{ route('generate') }}" class="btn-primary inline-flex mt-5" wire:navigate>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                </svg>
                Start Generating
            </a>
        </div>
    @endif

    <!-- Order History -->
    @if($recentOrders->count() > 0)
        <div>
            <h2 class="text-sm font-semibold mb-3" style="color: var(--text-primary);">Order History</h2>
            <div class="card overflow-hidden p-0">
                <table class="w-full">
                    <thead class="table-header">
                        <tr>
                            <th class="px-4 py-2.5 text-left text-xs font-medium" style="color: var(--text-muted);">Plan</th>
                            <th class="px-4 py-2.5 text-left text-xs font-medium hidden sm:table-cell" style="color: var(--text-muted);">Date</th>
                            <th class="px-4 py-2.5 text-left text-xs font-medium" style="color: var(--text-muted);">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($recentOrders as $order)
                            <tr class="table-row">
                                <td class="px-4 py-3">
                                    <p class="text-sm font-medium" style="color: var(--text-primary);">{{ $order->subscriptionPlan->name }}</p>
                                    <p class="text-xs" style="color: var(--accent);">{{ $order->subscriptionPlan->formatted_price }}</p>
                                </td>
                                <td class="px-4 py-3 hidden sm:table-cell">
                                    <p class="text-sm" style="color: var(--text-secondary);">{{ $order->created_at->format('d M Y') }}</p>
                                    <p class="text-[11px]" style="color: var(--text-muted);">{{ $order->created_at->format('H:i') }}</p>
                                </td>
                                <td class="px-4 py-3">
                                    @php
                                        $badgeClass = match($order->status_color) {
                                            'emerald', 'green' => 'badge-success',
                                            'amber', 'yellow' => 'badge-warning',
                                            'red' => 'badge-danger',
                                            default => 'badge-neutral',
                                        };
                                    @endphp
                                    <span class="{{ $badgeClass }}">{{ $order->status_label }}</span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    <!-- Payment Modal (QRIS) -->
    @if($showPaymentModal && $selectedPlan)
        <div class="modal-overlay">
            <div class="modal-backdrop" wire:click="closeModal"></div>

            <div class="modal-content max-w-md">
                <h2 class="text-base font-semibold mb-5" style="color: var(--text-primary);">Subscribe to {{ $selectedPlan->name }}</h2>

                @if($paymentPollingState === 'idle')
                    <!-- Step 1: Confirm -->
                    <div class="space-y-4">
                        <div class="p-4 rounded-xl" style="background-color: var(--accent-muted); border: 1px solid var(--accent-muted);">
                            <p class="text-[11px] font-semibold uppercase tracking-wide mb-1" style="color: var(--accent);">Plan Details</p>
                            <p class="text-sm font-semibold" style="color: var(--text-primary);">{{ $selectedPlan->name }}</p>
                            <p class="text-2xl font-bold mt-1" style="color: var(--accent);">{{ $selectedPlan->formatted_price }}</p>
                            <p class="text-xs mt-0.5" style="color: var(--text-secondary);">{{ $selectedPlan->duration_label }}</p>
                        </div>

                        <div class="p-4 rounded-xl" style="background-color: var(--bg-muted);">
                            <p class="text-sm font-medium mb-2" style="color: var(--text-primary);">Payment Method</p>
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-lg flex items-center justify-center" style="background-color: var(--accent-muted);">
                                    <svg class="w-5 h-5" style="color: var(--accent);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M16 8h2a2 2 0 012 2v6a2 2 0 01-2 2H6a2 2 0 01-2-2V6a2 2 0 012-2h2a2 2 0 012 2z"/>
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-sm font-medium" style="color: var(--text-primary);">QRIS (All Payment Methods)</p>
                                    <p class="text-xs" style="color: var(--text-muted);">Scan QR code to pay with any e-wallet or banking app</p>
                                </div>
                            </div>
                        </div>

                        <div class="flex gap-3 pt-2">
                            <button type="button" wire:click="closeModal" class="btn-secondary flex-1">Cancel</button>
                            <button type="button" wire:click="confirmPayment" class="btn-primary flex-1">Pay Now</button>
                        </div>
                    </div>
                @elseif($paymentPollingState === 'waiting')
                    <!-- Step 2: QRIS Payment -->
                    <div class="space-y-4">
                        <div class="text-center">
                            <p class="text-xs font-medium mb-3" style="color: var(--text-muted);">Total Payment</p>
                            <p class="text-3xl font-bold" style="color: var(--accent);">Rp {{ number_format($totalPayment ?? $selectedPlan->price, 0, ',', '.') }}</p>
                        </div>

                        @if($paymentQRIS)
                            <div class="text-center">
                                <div id="qris-container" class="inline-block p-4 rounded-xl" style="background-color: #fff; border: 1px solid var(--border-color);">
                                    <img src="https://api.qrserver.com/v1/create-qr-code/?size=250x250&data={{ urlencode($paymentQRIS) }}" 
                                         alt="QRIS QR Code"
                                         style="width: 250px; height: 250px; display: block; border-radius: 4px;"
                                         onerror="this.style.display='none';document.getElementById('qris-fallback').style.display='block'">
                                </div>
                                <div id="qris-fallback" class="p-3 rounded-lg text-center" style="display:none;background-color:var(--bg-muted);">
                                    <p class="text-xs font-medium mb-2" style="color:var(--text-primary);">Scan this QR code with your e-wallet app</p>
                                    <div class="text-center">
                                        <img src="{{ route('qr-code', ['data' => $paymentQRIS]) }}" 
                                             alt="QR Fallback" 
                                             style="width: 200px; height: 200px; display: inline-block; border-radius: 4px;">
                                    </div>
                                </div>
                                </div>
                                <p class="text-[11px] mt-3" style="color: var(--text-muted);">Scan QR code using your banking or e-wallet app</p>
                            </div>
                        @else
                            <div class="flex items-center justify-center py-8">
                                <div class="spinner"></div>
                                <span class="ml-2 text-sm" style="color: var(--text-muted);">Creating payment...</span>
                            </div>
                        @endif

                        <div class="text-center">
                            <button wire:click="checkPaymentStatus" class="btn-primary w-full justify-center">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                </svg>
                                Check Payment Status
                            </button>
                        </div>

                        <div class="flex items-center justify-center gap-2">
                            <div class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></div>
                            <span class="text-[11px]" style="color: var(--text-muted);">Waiting for payment...</span>
                        </div>

                        <div x-data="{ polling: false }"
                             x-init="polling = setInterval(() => { $wire.checkPaymentStatus() }, 5000);
                                    $watch('$wire.paymentPollingState', val => { if (val === 'completed') { clearInterval(polling); } })"
                             x-effect="if ($wire.paymentPollingState === 'completed') { clearInterval(polling); }"
                        ></div>

                        <button type="button" wire:click="closeModal" class="text-xs w-full text-center" style="color: var(--text-muted);">Close</button>
                    </div>
                @elseif($paymentPollingState === 'completed')
                    <!-- Step 3: Success -->
                    <div class="text-center space-y-4">
                        <div class="w-16 h-16 rounded-full flex items-center justify-center mx-auto" style="background-color: var(--success-muted);">
                            <svg class="w-8 h-8" style="color: var(--success);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-base font-semibold" style="color: var(--text-primary);">Payment Successful!</h3>
                            <p class="text-sm mt-1" style="color: var(--text-secondary);">Your subscription has been activated.</p>
                        </div>
                        <button wire:click="closeModal" class="btn-primary w-full justify-center">Continue</button>
                    </div>
                @elseif($paymentPollingState === 'error')
                    <!-- Step 4: Error -->
                    <div class="text-center space-y-4">
                        <div class="w-16 h-16 rounded-full flex items-center justify-center mx-auto" style="background-color: var(--danger-muted);">
                            <svg class="w-8 h-8" style="color: var(--danger);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-base font-semibold" style="color: var(--text-primary);">Failed to Create Payment</h3>
                            <p class="text-sm mt-1" style="color: var(--text-secondary);">Please try again later.</p>
                        </div>
                        <button wire:click="closeModal" class="btn-primary w-full justify-center">Close</button>
                    </div>
                @endif
            </div>
        </div>
    @endif
</div>
