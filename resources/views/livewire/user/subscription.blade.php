<div class="space-y-4">
    <div class="section-header">
        <h1 class="text-sm font-semibold" style="color: var(--text-primary);">Subscription</h1>
        <p class="text-xs" style="color: var(--text-muted);">Upgrade your account to unlock unlimited generations</p>
    </div>

    @if(session('success'))
        <div class="p-3 rounded-lg text-xs" style="background-color: var(--success-muted); border: 1px solid var(--success); color: var(--success);">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="p-3 rounded-lg text-xs" style="background-color: var(--danger-muted); border: 1px solid var(--danger); color: var(--danger);">
            {{ session('error') }}
        </div>
    @endif

    <div class="card">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
            <div>
                <h2 class="text-xs font-medium uppercase tracking-wide mb-2" style="color: var(--text-muted);">Current Status</h2>
                @if($isSubscribed)
                    <div class="flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full" style="background-color: var(--success);"></span>
                        <span class="text-sm font-medium" style="color: var(--success);">Subscribed</span>
                    </div>
                    @if($user->subscription_expires_at)
                        <p class="text-xs mt-1.5" style="color: var(--text-secondary);">
                            Valid until <strong>{{ $user->subscription_expires_at->format('d M Y') }}</strong>
                            ({{ $user->subscription_expires_at->diffForHumans() }})
                        </p>
                    @else
                        <p class="text-xs mt-1.5" style="color: var(--text-secondary);">Lifetime subscription</p>
                    @endif
                @else
                    <div class="flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full" style="background-color: var(--warning);"></span>
                        <span class="text-sm font-medium" style="color: var(--warning);">Free Account</span>
                    </div>
                    <p class="text-xs mt-1.5" style="color: var(--text-secondary);">
                        Limited to <strong>{{ $dailyLimit }}</strong> generations per day
                    </p>
                @endif
            </div>

            @if(!$isSubscribed)
                <div class="p-3 rounded-lg text-center" style="background-color: var(--bg-muted);">
                    <p class="text-xs mb-1" style="color: var(--text-muted);">Today's Usage</p>
                    <p class="text-lg font-semibold" style="color: var(--text-primary);">
                        {{ $todayCount }} / {{ $dailyLimit }}
                    </p>
                    @if($remainingGenerations > 0)
                        <p class="text-xs mt-0.5" style="color: var(--success);">
                            {{ $remainingGenerations }} remaining
                        </p>
                    @else
                        <p class="text-xs mt-0.5" style="color: var(--danger);">
                            {{ $remainingGenerations }} remaining
                        </p>
                    @endif
                </div>
            @endif
        </div>
    </div>

    @if($hasPendingOrder && $pendingOrder)
        <div class="card" style="border-color: var(--warning); background-color: var(--warning-muted);">
            <div class="flex items-start gap-3">
                <div class="icon-box-sm" style="background-color: var(--warning-muted);">
                    <svg class="w-4 h-4" style="color: var(--warning);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div class="flex-1">
                    <h3 class="text-xs font-medium" style="color: var(--warning);">Order Pending</h3>
                    <p class="text-xs mt-1" style="color: var(--text-secondary);">
                        Your order for <strong>{{ $pendingOrder->subscriptionPlan->name }}</strong> is waiting for admin approval.
                    </p>
                    <div class="flex items-center gap-3 mt-2">
                        <span class="text-xs" style="color: var(--text-muted);">
                            Submitted {{ $pendingOrder->created_at->diffForHumans() }}
                        </span>
                        <button
                            wire:click="cancelOrder({{ $pendingOrder->id }})"
                            wire:confirm="Are you sure you want to cancel this order?"
                            class="text-xs"
                            style="color: var(--danger);"
                        >
                            Cancel Order
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    @if(!$isSubscribed && !$hasPendingOrder)
        @if($plans->count() > 0)
            <div>
                <h2 class="text-xs font-medium uppercase tracking-wide mb-3" style="color: var(--text-muted);">Choose a Plan</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach($plans as $plan)
                        <div class="card hover:border-accent transition-colors group">
                            <div class="mb-3">
                                <h3 class="text-sm font-semibold group-hover:text-accent transition-colors" style="color: var(--text-primary);">{{ $plan->name }}</h3>
                                <p class="text-xs" style="color: var(--text-muted);">{{ $plan->duration_label }}</p>
                            </div>

                            <div class="mb-3">
                                <span class="text-xl font-bold" style="color: var(--accent);">{{ $plan->formatted_price }}</span>
                            </div>

                            @if($plan->description)
                                <p class="text-xs mb-3" style="color: var(--text-secondary);">{{ $plan->description }}</p>
                            @endif

                            <ul class="space-y-1.5 text-xs mb-4" style="color: var(--text-secondary);">
                                <li class="flex items-center gap-1.5">
                                    <svg class="w-3.5 h-3.5" style="color: var(--success);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                    </svg>
                                    Unlimited generations
                                </li>
                                <li class="flex items-center gap-1.5">
                                    <svg class="w-3.5 h-3.5" style="color: var(--success);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                    </svg>
                                    Full AI image analysis
                                </li>
                                <li class="flex items-center gap-1.5">
                                    <svg class="w-3.5 h-3.5" style="color: var(--success);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                    </svg>
                                    Priority support
                                </li>
                            </ul>

                            <button
                                wire:click="selectPlan({{ $plan->id }})"
                                class="btn-primary w-full justify-center text-sm"
                            >
                                Order Now
                            </button>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        @if($paymentMethods->count() > 0)
            <div>
                <h2 class="text-xs font-medium uppercase tracking-wide mb-3" style="color: var(--text-muted);">Payment Methods</h2>
                <div class="card">
                    <p class="text-xs mb-3" style="color: var(--text-secondary);">
                        After ordering, transfer to one of the following accounts and upload your proof of payment.
                    </p>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        @foreach($paymentMethods as $method)
                            <div class="p-3 rounded-lg flex items-center gap-3" style="background-color: var(--bg-muted);">
                                <div class="w-8 h-8 rounded flex items-center justify-center text-xs font-semibold shrink-0" style="background-color: var(--bg-card); color: var(--text-primary); border: 1px solid var(--border-color);">
                                    {{ strtoupper(substr($method->name, 0, 2)) }}
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-xs font-medium" style="color: var(--text-primary);">{{ $method->name }}</p>
                                    <p class="text-xs font-mono truncate" style="color: var(--text-secondary);">{{ $method->account_number }}</p>
                                    <p class="text-xs" style="color: var(--text-muted);">a.n. {{ $method->account_holder_name }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif
    @elseif($isSubscribed)
        <div class="card text-center py-8">
            <div class="w-12 h-12 rounded-full flex items-center justify-center mx-auto mb-3" style="background-color: var(--success-muted);">
                <svg class="w-6 h-6" style="color: var(--success);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <h3 class="text-sm font-semibold mb-1" style="color: var(--text-primary);">You're Subscribed!</h3>
            <p class="text-xs max-w-sm mx-auto" style="color: var(--text-secondary);">
                Enjoy unlimited metadata generations with full AI image analysis. Thank you for your support!
            </p>
            <a href="{{ route('generate') }}" class="btn-primary inline-flex text-sm mt-4" wire:navigate>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                </svg>
                Start Generating
            </a>
        </div>
    @endif

    @if($recentOrders->count() > 0)
        <div>
            <h2 class="text-xs font-medium uppercase tracking-wide mb-3" style="color: var(--text-muted);">Order History</h2>
            <div class="card p-0 overflow-hidden">
                <table class="w-full">
                    <thead class="table-header">
                        <tr>
                            <th class="px-3 py-2 text-left text-xs font-medium" style="color: var(--text-secondary);">Plan</th>
                            <th class="px-3 py-2 text-left text-xs font-medium hidden sm:table-cell" style="color: var(--text-secondary);">Date</th>
                            <th class="px-3 py-2 text-left text-xs font-medium" style="color: var(--text-secondary);">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($recentOrders as $order)
                            <tr class="table-row">
                                <td class="px-3 py-2">
                                    <p class="text-xs font-medium" style="color: var(--text-primary);">{{ $order->subscriptionPlan->name }}</p>
                                    <p class="text-xs" style="color: var(--accent);">{{ $order->subscriptionPlan->formatted_price }}</p>
                                </td>
                                <td class="px-3 py-2 hidden sm:table-cell">
                                    <p class="text-xs" style="color: var(--text-secondary);">{{ $order->created_at->format('d M Y') }}</p>
                                </td>
                                <td class="px-3 py-2">
                                    @php
                                        $badgeClass = match($order->status_color) {
                                            'emerald', 'green' => 'badge-success',
                                            'amber', 'yellow' => 'badge-warning',
                                            'red' => 'badge-danger',
                                            default => 'badge-neutral',
                                        };
                                    @endphp
                                    <span class="{{ $badgeClass }} text-xs">{{ $order->status_label }}</span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    @if($showOrderModal && $selectedPlan)
        <div class="modal-overlay">
            <div class="modal-backdrop" wire:click="closeModal"></div>

            <div class="modal-content max-w-md">
                <h2 class="text-sm font-semibold mb-4" style="color: var(--text-primary);">Order Subscription</h2>

                <form wire:submit="submitOrder" class="space-y-4">
                    <div class="p-3 rounded-lg" style="background-color: var(--bg-muted);">
                        <p class="text-xs font-medium uppercase tracking-wide mb-1" style="color: var(--text-muted);">Selected Plan</p>
                        <p class="text-sm font-semibold" style="color: var(--text-primary);">{{ $selectedPlan->name }}</p>
                        <p class="text-sm font-medium" style="color: var(--accent);">{{ $selectedPlan->formatted_price }}</p>
                        <p class="text-xs mt-0.5" style="color: var(--text-secondary);">{{ $selectedPlan->duration_label }}</p>
                    </div>

                    <div>
                        <label class="label">Payment Method <span style="color: var(--danger);">*</span></label>
                        <select wire:model="selectedPaymentMethodId" class="input text-sm">
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
                            <div class="p-3 rounded-lg" style="background-color: var(--accent-muted); border: 1px solid var(--accent);">
                                <p class="text-xs font-medium uppercase tracking-wide mb-1" style="color: var(--text-muted);">Transfer to</p>
                                <p class="text-xs font-semibold" style="color: var(--text-primary);">{{ $selectedMethod->name }}</p>
                                <p class="text-sm font-mono" style="color: var(--text-primary);">{{ $selectedMethod->account_number }}</p>
                                <p class="text-xs" style="color: var(--text-secondary);">a.n. {{ $selectedMethod->account_holder_name }}</p>
                            </div>
                        @endif
                    @endif

                    <div>
                        <label class="label">Proof of Payment (optional)</label>
                        <input
                            type="file"
                            wire:model="proofOfPayment"
                            accept="image/*"
                            class="input text-sm"
                        >
                        @error('proofOfPayment') <p class="mt-1 text-xs" style="color: var(--danger);">{{ $message }}</p> @enderror
                        @if($proofOfPayment)
                            <div class="mt-2">
                                <img src="{{ $proofOfPayment->temporaryUrl() }}" class="rounded max-h-24 object-contain">
                            </div>
                        @endif
                    </div>

                    <div>
                        <label class="label">Notes (optional)</label>
                        <textarea
                            wire:model="notes"
                            class="input text-sm"
                            rows="2"
                            placeholder="Transfer date, sender name, etc..."
                        ></textarea>
                        @error('notes') <p class="mt-1 text-xs" style="color: var(--danger);">{{ $message }}</p> @enderror
                    </div>

                    <div class="flex gap-2 pt-2">
                        <button type="button" wire:click="closeModal" class="btn-secondary flex-1 text-sm">
                            Cancel
                        </button>
                        <button type="submit" class="btn-primary flex-1 text-sm">
                            Submit Order
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
