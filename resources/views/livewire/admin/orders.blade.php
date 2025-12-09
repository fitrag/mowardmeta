<div class="space-y-8">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-3">
                <h1 class="text-2xl lg:text-3xl font-bold" style="color: var(--text-primary);">Subscription Orders</h1>
                @if($pendingCount > 0)
                    <span class="px-2.5 py-1 text-sm font-medium rounded-full bg-amber-500/20 text-amber-500">
                        {{ $pendingCount }} pending
                    </span>
                @endif
            </div>
            <p class="mt-1" style="color: var(--text-secondary);">Review and process subscription requests</p>
        </div>
    </div>

    <!-- Filters -->
    <div class="card">
        <div class="flex flex-col sm:flex-row gap-4">
            <div class="flex-1 relative">
                <svg class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5" style="color: var(--text-secondary);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <input 
                    type="text" 
                    wire:model.live.debounce.300ms="search"
                    placeholder="Search by user name or email..."
                    class="input pl-12"
                >
            </div>
            <select wire:model.live="statusFilter" class="input sm:w-48">
                <option value="">All Status</option>
                <option value="pending">Pending</option>
                <option value="approved">Approved</option>
                <option value="rejected">Rejected</option>
            </select>
        </div>
    </div>

    <!-- Table -->
    <div class="card overflow-hidden p-0">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead style="background-color: var(--bg-hover); border-bottom: 1px solid var(--border-color);">
                    <tr>
                        <th class="px-4 py-3 text-left text-sm font-medium" style="color: var(--text-secondary);">User</th>
                        <th class="px-4 py-3 text-left text-sm font-medium" style="color: var(--text-secondary);">Plan</th>
                        <th class="px-4 py-3 text-left text-sm font-medium hidden md:table-cell" style="color: var(--text-secondary);">Payment</th>
                        <th class="px-4 py-3 text-left text-sm font-medium hidden lg:table-cell" style="color: var(--text-secondary);">Date</th>
                        <th class="px-4 py-3 text-left text-sm font-medium" style="color: var(--text-secondary);">Status</th>
                        <th class="px-4 py-3 text-right text-sm font-medium" style="color: var(--text-secondary);">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($orders as $order)
                        <tr class="transition-colors" style="border-bottom: 1px solid var(--border-color);" onmouseover="this.style.backgroundColor='var(--bg-hover)'" onmouseout="this.style.backgroundColor='transparent'">
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-full bg-gradient-to-br from-primary-500 to-accent-cyan flex items-center justify-center font-semibold text-sm text-white">
                                        {{ strtoupper(substr($order->user->name, 0, 1)) }}
                                    </div>
                                    <div>
                                        <p class="font-medium" style="color: var(--text-primary);">{{ $order->user->name }}</p>
                                        <p class="text-sm" style="color: var(--text-secondary);">{{ $order->user->email }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                <p class="font-medium" style="color: var(--text-primary);">{{ $order->subscriptionPlan->name }}</p>
                                <p class="text-sm text-primary-500">{{ $order->subscriptionPlan->formatted_price }}</p>
                            </td>
                            <td class="px-4 py-3 hidden md:table-cell">
                                <p class="text-sm" style="color: var(--text-primary);">{{ $order->paymentMethod->name }}</p>
                            </td>
                            <td class="px-4 py-3 hidden lg:table-cell">
                                <p class="text-sm" style="color: var(--text-secondary);">{{ $order->created_at->format('d M Y') }}</p>
                                <p class="text-xs" style="color: var(--text-muted);">{{ $order->created_at->format('H:i') }}</p>
                            </td>
                            <td class="px-4 py-3">
                                <span class="px-2 py-1 rounded text-xs font-medium bg-{{ $order->status_color }}-500/20 text-{{ $order->status_color }}-500">
                                    {{ $order->status_label }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <button 
                                    wire:click="viewOrder({{ $order->id }})"
                                    class="px-3 py-1.5 text-sm font-medium rounded-lg transition-colors"
                                    style="background-color: var(--bg-hover); color: var(--text-primary);"
                                >
                                    View
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-12 text-center">
                                <div class="flex flex-col items-center">
                                    <svg class="w-12 h-12 mb-4" style="color: var(--text-muted);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                    </svg>
                                    <p class="font-medium" style="color: var(--text-primary);">No orders found</p>
                                    <p class="text-sm mt-1" style="color: var(--text-secondary);">Orders will appear here when users submit subscription requests</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($orders->hasPages())
            <div class="px-4 py-3" style="border-top: 1px solid var(--border-color);">
                {{ $orders->links() }}
            </div>
        @endif
    </div>

    <!-- Order Detail Modal -->
    @if($showModal && $viewingOrder)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" wire:click="closeModal"></div>
            
            <div class="relative rounded-2xl w-full max-w-lg p-6 animate-fade-in max-h-[90vh] overflow-y-auto" style="background-color: var(--bg-secondary); border: 1px solid var(--border-color);">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-xl font-bold" style="color: var(--text-primary);">Order Details</h2>
                    <span class="px-2.5 py-1 rounded text-sm font-medium bg-{{ $viewingOrder->status_color }}-500/20 text-{{ $viewingOrder->status_color }}-500">
                        {{ $viewingOrder->status_label }}
                    </span>
                </div>

                <div class="space-y-4">
                    <!-- User Info -->
                    <div class="p-4 rounded-xl" style="background-color: var(--bg-hover);">
                        <p class="text-xs font-medium uppercase tracking-wide mb-2" style="color: var(--text-muted);">User</p>
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full bg-gradient-to-br from-primary-500 to-accent-cyan flex items-center justify-center font-semibold text-sm text-white">
                                {{ strtoupper(substr($viewingOrder->user->name, 0, 1)) }}
                            </div>
                            <div>
                                <p class="font-medium" style="color: var(--text-primary);">{{ $viewingOrder->user->name }}</p>
                                <p class="text-sm" style="color: var(--text-secondary);">{{ $viewingOrder->user->email }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Plan & Payment -->
                    <div class="grid grid-cols-2 gap-4">
                        <div class="p-4 rounded-xl" style="background-color: var(--bg-hover);">
                            <p class="text-xs font-medium uppercase tracking-wide mb-2" style="color: var(--text-muted);">Plan</p>
                            <p class="font-medium" style="color: var(--text-primary);">{{ $viewingOrder->subscriptionPlan->name }}</p>
                            <p class="text-sm text-primary-500">{{ $viewingOrder->subscriptionPlan->formatted_price }}</p>
                            <p class="text-xs mt-1" style="color: var(--text-secondary);">{{ $viewingOrder->subscriptionPlan->duration_days }} days</p>
                        </div>
                        <div class="p-4 rounded-xl" style="background-color: var(--bg-hover);">
                            <p class="text-xs font-medium uppercase tracking-wide mb-2" style="color: var(--text-muted);">Payment Method</p>
                            <p class="font-medium" style="color: var(--text-primary);">{{ $viewingOrder->paymentMethod->name }}</p>
                            <p class="text-sm font-mono" style="color: var(--text-secondary);">{{ $viewingOrder->paymentMethod->account_number }}</p>
                        </div>
                    </div>

                    <!-- Proof of Payment -->
                    @if($viewingOrder->proof_of_payment)
                        <div class="p-4 rounded-xl" style="background-color: var(--bg-hover);">
                            <p class="text-xs font-medium uppercase tracking-wide mb-2" style="color: var(--text-muted);">Proof of Payment</p>
                            <a href="{{ Storage::url($viewingOrder->proof_of_payment) }}" target="_blank" class="block">
                                <img src="{{ Storage::url($viewingOrder->proof_of_payment) }}" alt="Proof of Payment" class="rounded-lg max-h-48 object-contain">
                            </a>
                        </div>
                    @endif

                    <!-- User Notes -->
                    @if($viewingOrder->notes)
                        <div class="p-4 rounded-xl" style="background-color: var(--bg-hover);">
                            <p class="text-xs font-medium uppercase tracking-wide mb-2" style="color: var(--text-muted);">User Notes</p>
                            <p class="text-sm" style="color: var(--text-primary);">{{ $viewingOrder->notes }}</p>
                        </div>
                    @endif

                    <!-- Order Date -->
                    <div class="p-4 rounded-xl" style="background-color: var(--bg-hover);">
                        <p class="text-xs font-medium uppercase tracking-wide mb-2" style="color: var(--text-muted);">Order Date</p>
                        <p class="text-sm" style="color: var(--text-primary);">{{ $viewingOrder->created_at->format('d F Y, H:i') }}</p>
                    </div>

                    @if($viewingOrder->isPending())
                        <!-- Admin Notes -->
                        <div>
                            <label class="label">Admin Notes (optional)</label>
                            <textarea wire:model="adminNotes" class="input" rows="2" placeholder="Add notes..."></textarea>
                        </div>

                        <!-- Action Buttons -->
                        <div class="flex gap-3 pt-2">
                            <button 
                                wire:click="rejectOrder"
                                wire:confirm="Are you sure you want to reject this order?"
                                class="flex-1 py-2.5 rounded-xl font-medium bg-red-500/20 text-red-500 hover:bg-red-500/30 transition-colors"
                            >
                                Reject
                            </button>
                            <button 
                                wire:click="approveOrder"
                                class="flex-1 py-2.5 rounded-xl font-medium bg-emerald-500 text-white hover:bg-emerald-400 transition-colors"
                            >
                                Approve
                            </button>
                        </div>
                    @else
                        <!-- Processed Info -->
                        <div class="p-4 rounded-xl border {{ $viewingOrder->isApproved() ? 'border-emerald-500/30 bg-emerald-500/10' : 'border-red-500/30 bg-red-500/10' }}">
                            <p class="text-xs font-medium uppercase tracking-wide mb-2" style="color: var(--text-muted);">Processed</p>
                            <p class="text-sm" style="color: var(--text-primary);">
                                {{ $viewingOrder->status_label }} by {{ $viewingOrder->processedByUser?->name ?? 'Unknown' }}
                            </p>
                            <p class="text-xs mt-1" style="color: var(--text-secondary);">
                                {{ $viewingOrder->processed_at?->format('d F Y, H:i') }}
                            </p>
                            @if($viewingOrder->admin_notes)
                                <p class="text-sm mt-2 pt-2" style="color: var(--text-secondary); border-top: 1px solid var(--border-color);">
                                    {{ $viewingOrder->admin_notes }}
                                </p>
                            @endif
                        </div>

                        <button wire:click="closeModal" class="btn-secondary w-full">
                            Close
                        </button>
                    @endif
                </div>
            </div>
        </div>
    @endif
</div>
