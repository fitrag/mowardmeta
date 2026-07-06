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

    <!-- Pending Order Alert -->
    @if($hasPendingOrder && $pendingOrder)
        <div class="card" style="border-color: var(--warning-muted);">
            <div class="flex items-start gap-3">
                <div class="w-10 h-10 rounded-lg flex items-center justify-center flex-shrink-0" style="background-color: var(--warning-muted);">
                    <svg class="w-5 h-5" style="color: var(--warning);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div class="flex-1">
                    <div class="flex items-center justify-between">
                        <h3 class="text-sm font-semibold" style="color: var(--warning);">Order Pending</h3>
                        <button
                            wire:click="cancelOrder({{ $pendingOrder->id }})"
                            wire:confirm="Are you sure you want to cancel this order?"
                            class="text-xs flex items-center gap-1 px-2 py-1 rounded-md transition-colors"
                            style="color: var(--danger);"
                            onmouseover="this.style.backgroundColor='var(--danger-muted)'"
                            onmouseout="this.style.backgroundColor='transparent'"
                        >
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                            Cancel
                        </button>
                    </div>
                    <p class="text-xs mt-1" style="color: var(--text-secondary);">
                        Your order for <strong style="color: var(--text-primary);">{{ $pendingOrder->subscriptionPlan->name }}</strong> is waiting for admin approval.
                    </p>
                    <p class="text-[11px] mt-2" style="color: var(--text-muted);">
                        Submitted {{ $pendingOrder->created_at->diffForHumans() }}
                    </p>
                </div>
            </div>
        </div>
    @endif

    <!-- Plans -->
    @if(!$isSubscribed && !$hasPendingOrder && $plans->count() > 0)
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
                                Order Now
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Payment Methods -->
        @if($paymentMethods->count() > 0)
            <div>
                <h2 class="text-sm font-semibold mb-3" style="color: var(--text-primary);">Payment Methods</h2>
                <div class="card">
                    <p class="text-xs mb-4" style="color: var(--text-secondary);">
                        After ordering, transfer to one of the following accounts and upload your proof of payment.
                    </p>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        @foreach($paymentMethods as $method)
                            <div class="flex items-center gap-3 p-3 rounded-xl" style="background-color: var(--bg-muted);">
                                <div class="w-10 h-10 rounded-lg flex items-center justify-center text-sm font-bold flex-shrink-0" style="background-color: var(--bg-card); border: 1px solid var(--border-color); color: var(--text-primary);">
                                    {{ strtoupper(substr($method->name, 0, 2)) }}
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium" style="color: var(--text-primary);">{{ $method->name }}</p>
                                    <p class="text-xs font-mono truncate" style="color: var(--text-secondary);">{{ $method->account_number }}</p>
                                    <p class="text-[11px]" style="color: var(--text-muted);">a.n. {{ $method->account_holder_name }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif
    @elseif($isSubscribed)
        <!-- Subscribed CTA -->
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

    <!-- Order Modal -->
    @if($showOrderModal && $selectedPlan)
        <div class="modal-overlay">
            <div class="modal-backdrop" wire:click="closeModal"></div>

            <div class="modal-content max-w-md">
                <h2 class="text-base font-semibold mb-5" style="color: var(--text-primary);">Complete Your Order</h2>

                <form wire:submit="submitOrder" class="space-y-4">
                    <!-- Selected Plan -->
                    <div class="p-4 rounded-xl" style="background-color: var(--accent-muted); border: 1px solid var(--accent-muted);">
                        <p class="text-[11px] font-semibold uppercase tracking-wide mb-1" style="color: var(--accent);">Selected Plan</p>
                        <p class="text-sm font-semibold" style="color: var(--text-primary);">{{ $selectedPlan->name }}</p>
                        <p class="text-2xl font-bold mt-1" style="color: var(--accent);">{{ $selectedPlan->formatted_price }}</p>
                        <p class="text-xs mt-0.5" style="color: var(--text-secondary);">{{ $selectedPlan->duration_label }}</p>
                    </div>

                    <!-- Payment Method -->
                    <div>
                        <label class="label">Payment Method <span style="color: var(--danger);">*</span></label>
                        <select wire:model="selectedPaymentMethodId" class="input">
                            <option value="">Select payment method...</option>
                            @foreach($paymentMethods as $method)
                                <option value="{{ $method->id }}">{{ $method->name }} - {{ $method->account_number }}</option>
                            @endforeach
                        </select>
                        @error('selectedPaymentMethodId') <p class="mt-1 text-xs" style="color: var(--danger);">{{ $message }}</p> @enderror
                    </div>

                    @if($selectedPaymentMethodId)
                        @php $selectedMethod = $paymentMethods->find($selectedPaymentMethodId); @endphp
                        @if($selectedMethod)
                            <div class="p-4 rounded-xl" style="background-color: var(--bg-muted);">
                                <p class="text-[11px] font-semibold uppercase tracking-wide mb-2" style="color: var(--text-muted);">Transfer to</p>
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-lg flex items-center justify-center text-sm font-bold flex-shrink-0" style="background-color: var(--bg-card); border: 1px solid var(--border-color); color: var(--text-primary);">
                                        {{ strtoupper(substr($selectedMethod->name, 0, 2)) }}
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium" style="color: var(--text-primary);">{{ $selectedMethod->name }}</p>
                                        <p class="text-sm font-mono" style="color: var(--accent);">{{ $selectedMethod->account_number }}</p>
                                        <p class="text-xs" style="color: var(--text-muted);">a.n. {{ $selectedMethod->account_holder_name }}</p>
                                    </div>
                                </div>
                            </div>
                        @endif
                    @endif

                    <!-- Proof of Payment -->
                    <div>
                        <label class="label">Proof of Payment</label>
                        <input
                            type="file"
                            wire:model="proofOfPayment"
                            accept="image/*"
                            class="input text-sm"
                        >
                        @error('proofOfPayment') <p class="mt-1 text-xs" style="color: var(--danger);">{{ $message }}</p> @enderror
                        @if($proofOfPayment)
                            <div class="mt-2 rounded-lg overflow-hidden" style="border: 1px solid var(--border-color);">
                                <img src="{{ $proofOfPayment->temporaryUrl() }}" class="w-full max-h-32 object-contain" style="background-color: var(--bg-card);">
                            </div>
                        @endif
                    </div>

                    <!-- Notes -->
                    <div>
                        <label class="label">Notes (optional)</label>
                        <textarea
                            wire:model="notes"
                            class="input"
                            rows="2"
                            placeholder="Transfer date, sender name, etc..."
                        ></textarea>
                        @error('notes') <p class="mt-1 text-xs" style="color: var(--danger);">{{ $message }}</p> @enderror
                    </div>

                    <div class="flex gap-3 pt-2">
                        <button type="button" wire:click="closeModal" class="btn-secondary flex-1">Cancel</button>
                        <button type="submit" class="btn-primary flex-1">Submit Order</button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
