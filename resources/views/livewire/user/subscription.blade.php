<div class="space-y-8">
    <!-- Header -->
    <div>
        <h1 class="text-2xl lg:text-3xl font-bold" style="color: var(--text-primary);">Subscription</h1>
        <p class="mt-1" style="color: var(--text-secondary);">Upgrade your account to unlock unlimited generations</p>
    </div>

    <!-- Flash Messages -->
    @if(session('success'))
        <div class="p-4 rounded-xl bg-emerald-500/10 border border-emerald-500/20">
            <p class="text-sm text-emerald-500">{{ session('success') }}</p>
        </div>
    @endif

    @if(session('error'))
        <div class="p-4 rounded-xl bg-red-500/10 border border-red-500/20">
            <p class="text-sm text-red-500">{{ session('error') }}</p>
        </div>
    @endif

    <!-- Current Status -->
    <div class="card">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h2 class="text-lg font-bold mb-2" style="color: var(--text-primary);">Current Status</h2>
                @if($isSubscribed)
                    <div class="flex items-center gap-3">
                        <span class="w-3 h-3 rounded-full bg-emerald-500"></span>
                        <span class="text-lg font-medium text-emerald-500">Subscribed</span>
                    </div>
                    @if($user->subscription_expires_at)
                        <p class="text-sm mt-2" style="color: var(--text-secondary);">
                            Valid until <strong>{{ $user->subscription_expires_at->format('d F Y') }}</strong>
                            ({{ $user->subscription_expires_at->diffForHumans() }})
                        </p>
                    @else
                        <p class="text-sm mt-2" style="color: var(--text-secondary);">Lifetime subscription</p>
                    @endif
                @else
                    <div class="flex items-center gap-3">
                        <span class="w-3 h-3 rounded-full bg-amber-500"></span>
                        <span class="text-lg font-medium text-amber-500">Free Account</span>
                    </div>
                    <p class="text-sm mt-2" style="color: var(--text-secondary);">
                        Limited to <strong>{{ $dailyLimit }}</strong> generations per day
                    </p>
                @endif
            </div>
            
            @if(!$isSubscribed)
                <div class="p-4 rounded-xl text-center" style="background-color: var(--bg-hover);">
                    <p class="text-sm mb-1" style="color: var(--text-secondary);">Today's Usage</p>
                    <p class="text-2xl font-bold" style="color: var(--text-primary);">
                        {{ $todayCount }} / {{ $dailyLimit }}
                    </p>
                    <p class="text-sm mt-1 {{ $remainingGenerations > 0 ? 'text-emerald-500' : 'text-red-500' }}">
                        {{ $remainingGenerations }} remaining
                    </p>
                </div>
            @endif
        </div>
    </div>

    <!-- Pending Order Notice -->
    @if($hasPendingOrder && $pendingOrder)
        <div class="card border-amber-500/30 bg-amber-500/5">
            <div class="flex items-start gap-4">
                <div class="w-10 h-10 rounded-xl bg-amber-500/20 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div class="flex-1">
                    <h3 class="font-bold text-amber-600">Order Pending</h3>
                    <p class="text-sm mt-1" style="color: var(--text-secondary);">
                        Your order for <strong>{{ $pendingOrder->subscriptionPlan->name }}</strong> is waiting for admin approval.
                    </p>
                    <div class="flex items-center gap-4 mt-3">
                        <span class="text-xs" style="color: var(--text-muted);">
                            Submitted {{ $pendingOrder->created_at->diffForHumans() }}
                        </span>
                        <button 
                            wire:click="cancelOrder({{ $pendingOrder->id }})"
                            wire:confirm="Are you sure you want to cancel this order?"
                            class="text-xs text-red-500 hover:text-red-400"
                        >
                            Cancel Order
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    @if(!$isSubscribed && !$hasPendingOrder)
        <!-- Subscription Plans -->
        @if($plans->count() > 0)
            <div>
                <h2 class="text-lg font-bold mb-4" style="color: var(--text-primary);">Choose a Plan</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($plans as $plan)
                        <div class="card hover:border-primary-500 transition-colors group">
                            <div class="mb-4">
                                <h3 class="text-xl font-bold group-hover:text-primary-500 transition-colors" style="color: var(--text-primary);">{{ $plan->name }}</h3>
                                <p class="text-sm" style="color: var(--text-secondary);">{{ $plan->duration_label }}</p>
                            </div>
                            
                            <div class="mb-4">
                                <span class="text-3xl font-bold text-primary-500">{{ $plan->formatted_price }}</span>
                            </div>
                            
                            @if($plan->description)
                                <p class="text-sm mb-4" style="color: var(--text-secondary);">{{ $plan->description }}</p>
                            @endif
                            
                            <ul class="space-y-2 text-sm mb-6" style="color: var(--text-secondary);">
                                <li class="flex items-center gap-2">
                                    <svg class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                    </svg>
                                    Unlimited generations
                                </li>
                                <li class="flex items-center gap-2">
                                    <svg class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                    </svg>
                                    Full AI image analysis
                                </li>
                                <li class="flex items-center gap-2">
                                    <svg class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                    </svg>
                                    Priority support
                                </li>
                            </ul>

                            <button 
                                wire:click="selectPlan({{ $plan->id }})"
                                class="btn-primary w-full justify-center"
                            >
                                Order Now
                            </button>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- Payment Methods Info -->
        @if($paymentMethods->count() > 0)
            <div>
                <h2 class="text-lg font-bold mb-4" style="color: var(--text-primary);">Payment Methods</h2>
                <div class="card">
                    <p class="text-sm mb-4" style="color: var(--text-secondary);">
                        After ordering, transfer to one of the following accounts and upload your proof of payment.
                    </p>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        @foreach($paymentMethods as $method)
                            <div class="p-4 rounded-xl flex items-center gap-4" style="background-color: var(--bg-hover);">
                                <div class="w-10 h-10 rounded-lg flex items-center justify-center font-bold text-sm shrink-0" style="background-color: var(--bg-card); color: var(--text-primary);">
                                    {{ strtoupper(substr($method->name, 0, 2)) }}
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="font-medium" style="color: var(--text-primary);">{{ $method->name }}</p>
                                    <p class="text-sm font-mono truncate" style="color: var(--text-secondary);">{{ $method->account_number }}</p>
                                    <p class="text-xs" style="color: var(--text-muted);">a.n. {{ $method->account_holder_name }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif
    @elseif($isSubscribed)
        <!-- Subscribed User -->
        <div class="card text-center py-12">
            <div class="w-16 h-16 rounded-full bg-emerald-500/20 flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <h3 class="text-xl font-bold mb-2" style="color: var(--text-primary);">You're Subscribed!</h3>
            <p class="text-sm max-w-md mx-auto" style="color: var(--text-secondary);">
                Enjoy unlimited metadata generations with full AI image analysis. Thank you for your support!
            </p>
            <a href="{{ route('generate') }}" class="btn-primary inline-flex mt-6" wire:navigate>
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                </svg>
                Start Generating
            </a>
        </div>
    @endif

    <!-- Order History -->
    @if($recentOrders->count() > 0)
        <div>
            <h2 class="text-lg font-bold mb-4" style="color: var(--text-primary);">Order History</h2>
            <div class="card p-0 overflow-hidden">
                <table class="w-full">
                    <thead style="background-color: var(--bg-hover); border-bottom: 1px solid var(--border-color);">
                        <tr>
                            <th class="px-4 py-3 text-left text-sm font-medium" style="color: var(--text-secondary);">Plan</th>
                            <th class="px-4 py-3 text-left text-sm font-medium hidden sm:table-cell" style="color: var(--text-secondary);">Date</th>
                            <th class="px-4 py-3 text-left text-sm font-medium" style="color: var(--text-secondary);">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($recentOrders as $order)
                            <tr style="border-bottom: 1px solid var(--border-color);">
                                <td class="px-4 py-3">
                                    <p class="font-medium" style="color: var(--text-primary);">{{ $order->subscriptionPlan->name }}</p>
                                    <p class="text-sm text-primary-500">{{ $order->subscriptionPlan->formatted_price }}</p>
                                </td>
                                <td class="px-4 py-3 hidden sm:table-cell">
                                    <p class="text-sm" style="color: var(--text-secondary);">{{ $order->created_at->format('d M Y') }}</p>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="px-2 py-1 rounded text-xs font-medium bg-{{ $order->status_color }}-500/20 text-{{ $order->status_color }}-500">
                                        {{ $order->status_label }}
                                    </span>
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
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" wire:click="closeModal"></div>
            
            <div class="relative rounded-2xl w-full max-w-md p-6 animate-fade-in max-h-[90vh] overflow-y-auto" style="background-color: var(--bg-secondary); border: 1px solid var(--border-color);">
                <h2 class="text-xl font-bold mb-6" style="color: var(--text-primary);">Order Subscription</h2>

                <form wire:submit="submitOrder" class="space-y-5">
                    <!-- Selected Plan -->
                    <div class="p-4 rounded-xl" style="background-color: var(--bg-hover);">
                        <p class="text-xs font-medium uppercase tracking-wide mb-2" style="color: var(--text-muted);">Selected Plan</p>
                        <p class="font-bold text-lg" style="color: var(--text-primary);">{{ $selectedPlan->name }}</p>
                        <p class="text-primary-500 font-medium">{{ $selectedPlan->formatted_price }}</p>
                        <p class="text-sm mt-1" style="color: var(--text-secondary);">{{ $selectedPlan->duration_label }}</p>
                    </div>

                    <!-- Payment Method -->
                    <div>
                        <label class="label">Payment Method <span class="text-red-500">*</span></label>
                        <select wire:model="selectedPaymentMethodId" class="input">
                            <option value="">Select payment method...</option>
                            @foreach($paymentMethods as $method)
                                <option value="{{ $method->id }}">{{ $method->name }} - {{ $method->account_number }}</option>
                            @endforeach
                        </select>
                        @error('selectedPaymentMethodId') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                    </div>

                    <!-- Show selected payment details -->
                    @if($selectedPaymentMethodId)
                        @php $selectedMethod = $paymentMethods->find($selectedPaymentMethodId); @endphp
                        @if($selectedMethod)
                            <div class="p-4 rounded-xl border border-primary-500/30 bg-primary-500/5">
                                <p class="text-xs font-medium uppercase tracking-wide mb-2" style="color: var(--text-muted);">Transfer to</p>
                                <p class="font-bold" style="color: var(--text-primary);">{{ $selectedMethod->name }}</p>
                                <p class="text-lg font-mono" style="color: var(--text-primary);">{{ $selectedMethod->account_number }}</p>
                                <p class="text-sm" style="color: var(--text-secondary);">a.n. {{ $selectedMethod->account_holder_name }}</p>
                            </div>
                        @endif
                    @endif

                    <!-- Proof of Payment -->
                    <div>
                        <label class="label">Proof of Payment (optional)</label>
                        <input 
                            type="file" 
                            wire:model="proofOfPayment" 
                            accept="image/*"
                            class="input file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-primary-500/10 file:text-primary-500 hover:file:bg-primary-500/20"
                        >
                        @error('proofOfPayment') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                        @if($proofOfPayment)
                            <div class="mt-2">
                                <img src="{{ $proofOfPayment->temporaryUrl() }}" class="rounded-lg max-h-32 object-contain">
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
                        @error('notes') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                    </div>

                    <div class="flex gap-3 pt-2">
                        <button type="button" wire:click="closeModal" class="btn-secondary flex-1">
                            Cancel
                        </button>
                        <button type="submit" class="btn-primary flex-1">
                            Submit Order
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
