<div class="space-y-6 sm:space-y-8">
    <!-- Header -->
    <div>
        <h1 class="text-xl sm:text-2xl font-bold" style="color: var(--text-primary);">License Store</h1>
        <p class="mt-1 text-sm" style="color: var(--text-secondary);">Purchase licenses for our products</p>
    </div>

    @if(session('success'))
        <div class="p-3 sm:p-4 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-500 text-sm sm:text-base">
            {{ session('success') }}
        </div>
    @endif

    <!-- My Licenses -->
    @if($myLicenses->count() > 0)
        <div class="card">
            <h2 class="text-base sm:text-lg font-bold mb-3 sm:mb-4" style="color: var(--text-primary);">My Licenses</h2>
            <div class="space-y-3">
                @foreach($myLicenses as $license)
                    <div class="p-3 sm:p-4 rounded-xl" style="background-color: var(--bg-hover);">
                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                            <div class="flex-1 min-w-0">
                                <div class="flex flex-wrap items-center gap-2">
                                    <p class="font-medium" style="color: var(--text-primary);">{{ $license->product_name }}</p>
                                    <span class="px-2 py-0.5 text-xs rounded-full bg-{{ $license->status_color }}-500/20 text-{{ $license->status_color }}-500">
                                        {{ $license->status_label }}
                                    </span>
                                    @if($license->license_type === 'credits')
                                        <span class="px-2 py-0.5 text-xs rounded-full bg-purple-500/20 text-purple-500">Credits</span>
                                    @else
                                        <span class="px-2 py-0.5 text-xs rounded-full bg-blue-500/20 text-blue-500">Duration</span>
                                    @endif
                                </div>
                                <p class="text-xs sm:text-sm font-mono mt-1 break-all" style="color: var(--text-secondary);">{{ $license->license_key }}</p>
                                @if($license->license_type === 'credits')
                                    <p class="text-xs mt-1" style="color: var(--text-muted);">
                                        Credits: <span class="font-medium" style="color: var(--text-primary);">{{ $license->getCreditsRemaining() ?? '∞' }}</span> / {{ $license->credits_total ?? '∞' }} remaining
                                    </p>
                                @elseif($license->expires_at)
                                    <p class="text-xs mt-1" style="color: var(--text-muted);">
                                        Expires: {{ $license->expires_at->format('d M Y') }}
                                        @if($license->days_remaining !== null)
                                            ({{ $license->days_remaining }} days left)
                                        @endif
                                    </p>
                                @else
                                    <p class="text-xs mt-1" style="color: var(--text-muted);">Lifetime license</p>
                                @endif
                            </div>
                            <button onclick="navigator.clipboard.writeText('{{ $license->license_key }}')" class="btn-secondary text-xs sm:text-sm w-full sm:w-auto">
                                Copy Key
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <!-- Pending Orders -->
    @if($pendingOrders->count() > 0)
        <div class="card border-amber-500/30">
            <h2 class="text-base sm:text-lg font-bold mb-3 sm:mb-4 text-amber-500">Pending Orders</h2>
            <div class="space-y-3">
                @foreach($pendingOrders as $order)
                    <div class="p-3 sm:p-4 rounded-xl" style="background-color: var(--bg-hover);">
                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 sm:gap-4">
                            <div>
                                <p class="font-medium" style="color: var(--text-primary);">{{ $order->licensePlan->name }}</p>
                                <p class="text-sm" style="color: var(--text-secondary);">{{ $order->licensePlan->formatted_price }}</p>
                                <p class="text-xs mt-1" style="color: var(--text-muted);">Ordered {{ $order->created_at->diffForHumans() }}</p>
                            </div>
                            <button wire:click="cancelOrder({{ $order->id }})" wire:confirm="Cancel this order?" class="text-red-500 hover:text-red-400 text-sm self-start sm:self-center">
                                Cancel
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <!-- Available Plans -->
    <div>
        <h2 class="text-base sm:text-lg font-bold mb-3 sm:mb-4" style="color: var(--text-primary);">Available Plans</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6">
            @forelse($plans as $plan)
                <div class="card hover:border-primary-500/50 transition-colors">
                    <div class="flex items-start justify-between gap-2 mb-3 sm:mb-4">
                        <div class="min-w-0">
                            <h3 class="text-lg sm:text-xl font-bold truncate" style="color: var(--text-primary);">{{ $plan->name }}</h3>
                            <p class="text-xs sm:text-sm truncate" style="color: var(--text-secondary);">{{ $plan->product_name }}</p>
                        </div>
                        <span class="px-2 py-1 text-xs rounded-full flex-shrink-0 {{ $plan->license_type === 'credits' ? 'bg-purple-500/20 text-purple-500' : 'bg-blue-500/20 text-blue-500' }}">
                            {{ $plan->license_type_label }}
                        </span>
                    </div>

                    <div class="mb-4 sm:mb-6">
                        @if($plan->price <= 0)
                            <span class="text-2xl sm:text-3xl font-bold text-emerald-500">GRATIS</span>
                        @else
                            <span class="text-2xl sm:text-3xl font-bold text-primary-500">{{ $plan->formatted_price }}</span>
                        @endif
                        <span class="text-xs sm:text-sm" style="color: var(--text-secondary);">/ {{ $plan->duration_label }}</span>
                    </div>

                    @if($plan->description)
                        <p class="text-xs sm:text-sm mb-3 sm:mb-4" style="color: var(--text-secondary);">{{ $plan->description }}</p>
                    @endif

                    @if($plan->features)
                        <ul class="space-y-1.5 sm:space-y-2 mb-4 sm:mb-6">
                            @foreach($plan->features as $feature)
                                <li class="flex items-start gap-2 text-xs sm:text-sm" style="color: var(--text-secondary);">
                                    <svg class="w-4 h-4 text-emerald-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                    </svg>
                                    <span>{{ $feature }}</span>
                                </li>
                            @endforeach
                        </ul>
                    @endif

                    <div class="space-y-1.5 sm:space-y-2 text-xs sm:text-sm mb-4 sm:mb-6" style="color: var(--text-muted);">
                        <p>✓ {{ $plan->max_activations }} device activation(s)</p>
                        @if($plan->license_type === 'credits')
                            <p>✓ {{ $plan->credits_amount }} credits</p>
                        @else
                            <p>✓ {{ $plan->duration_days }} days validity</p>
                        @endif
                    </div>

                    <button wire:click="selectPlan({{ $plan->id }})" class="w-full btn-primary text-sm sm:text-base">
                        {{ $plan->price <= 0 ? 'Get Free' : 'Purchase' }}
                    </button>
                </div>
            @empty
                <div class="col-span-full text-center py-8 sm:py-12">
                    <p class="text-sm" style="color: var(--text-secondary);">No plans available at the moment.</p>
                </div>
            @endforelse
        </div>
    </div>

    <!-- Recent Orders (Mobile Card / Desktop Table) -->
    @if($recentOrders->count() > 0)
        <div class="card">
            <h2 class="text-base sm:text-lg font-bold mb-3 sm:mb-4" style="color: var(--text-primary);">Order History</h2>
            
            <!-- Mobile View -->
            <div class="sm:hidden space-y-3">
                @foreach($recentOrders as $order)
                    <div class="p-3 rounded-xl" style="background-color: var(--bg-hover);">
                        <div class="flex items-start justify-between gap-2 mb-2">
                            <div>
                                <p class="font-medium text-sm" style="color: var(--text-primary);">{{ $order->licensePlan->name }}</p>
                                <p class="text-xs" style="color: var(--text-secondary);">{{ $order->licensePlan->formatted_price }}</p>
                            </div>
                            <span class="px-2 py-0.5 text-xs rounded-full bg-{{ $order->status_color }}-500/20 text-{{ $order->status_color }}-500">
                                {{ $order->status_label }}
                            </span>
                        </div>
                        @if($order->license)
                            <p class="text-xs font-mono break-all mb-1" style="color: var(--text-muted);">{{ $order->license->license_key }}</p>
                        @endif
                        <p class="text-xs" style="color: var(--text-muted);">{{ $order->created_at->format('d M Y') }}</p>
                    </div>
                @endforeach
            </div>

            <!-- Desktop View -->
            <div class="hidden sm:block overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr style="border-bottom: 1px solid var(--border-color);">
                            <th class="text-left p-3 text-sm font-medium" style="color: var(--text-secondary);">Plan</th>
                            <th class="text-left p-3 text-sm font-medium" style="color: var(--text-secondary);">Status</th>
                            <th class="text-left p-3 text-sm font-medium" style="color: var(--text-secondary);">License Key</th>
                            <th class="text-left p-3 text-sm font-medium" style="color: var(--text-secondary);">Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($recentOrders as $order)
                            <tr style="border-bottom: 1px solid var(--border-color);">
                                <td class="p-3">
                                    <p class="font-medium" style="color: var(--text-primary);">{{ $order->licensePlan->name }}</p>
                                    <p class="text-sm" style="color: var(--text-secondary);">{{ $order->licensePlan->formatted_price }}</p>
                                </td>
                                <td class="p-3">
                                    <span class="px-2 py-1 text-xs rounded-full bg-{{ $order->status_color }}-500/20 text-{{ $order->status_color }}-500">
                                        {{ $order->status_label }}
                                    </span>
                                </td>
                                <td class="p-3">
                                    @if($order->license)
                                        <code class="text-xs font-mono" style="color: var(--text-primary);">{{ $order->license->license_key }}</code>
                                    @else
                                        <span class="text-sm" style="color: var(--text-muted);">-</span>
                                    @endif
                                </td>
                                <td class="p-3 text-sm" style="color: var(--text-secondary);">
                                    {{ $order->created_at->format('d M Y') }}
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
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-modal="true">
            <div class="flex items-end sm:items-center justify-center min-h-screen p-0 sm:p-4">
                <div class="fixed inset-0 bg-black/50" wire:click="closeModal"></div>
                
                <div class="relative w-full sm:max-w-md card rounded-b-none sm:rounded-b-2xl">
                    <h2 class="text-lg sm:text-xl font-bold mb-1 sm:mb-2" style="color: var(--text-primary);">Purchase License</h2>
                    <p class="text-xs sm:text-sm mb-4 sm:mb-6" style="color: var(--text-secondary);">{{ $selectedPlan->name }} - {{ $selectedPlan->formatted_price }}</p>

                    <form wire:submit="submitOrder" class="space-y-3 sm:space-y-4">
                        <div>
                            <label class="block text-xs sm:text-sm font-medium mb-1.5 sm:mb-2" style="color: var(--text-secondary);">Payment Method</label>
                            <select wire:model="selectedPaymentMethodId" class="input w-full text-sm">
                                <option value="">Select payment method...</option>
                                @foreach($paymentMethods as $method)
                                    <option value="{{ $method->id }}">{{ $method->name }}</option>
                                @endforeach
                            </select>
                            @error('selectedPaymentMethodId') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-xs sm:text-sm font-medium mb-1.5 sm:mb-2" style="color: var(--text-secondary);">Proof of Payment (optional)</label>
                            <input type="file" wire:model="proofOfPayment" accept="image/*" class="input w-full text-sm">
                            @error('proofOfPayment') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            
                            @if($proofOfPayment)
                                <img src="{{ $proofOfPayment->temporaryUrl() }}" class="mt-2 max-h-24 sm:max-h-32 rounded-lg">
                            @endif
                        </div>

                        <div>
                            <label class="block text-xs sm:text-sm font-medium mb-1.5 sm:mb-2" style="color: var(--text-secondary);">Notes (optional)</label>
                            <textarea wire:model="notes" class="input w-full text-sm" rows="2" placeholder="Any additional information..."></textarea>
                        </div>

                        <div class="flex flex-col-reverse sm:flex-row justify-end gap-2 sm:gap-3 pt-3 sm:pt-4">
                            <button type="button" wire:click="closeModal" class="btn-secondary w-full sm:w-auto">Cancel</button>
                            <button type="submit" class="btn-primary w-full sm:w-auto">Submit Order</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
</div>
