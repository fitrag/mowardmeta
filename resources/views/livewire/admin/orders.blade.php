<div class="space-y-6">
    <!-- Header -->
    <div class="section-header">
        <div class="flex items-center gap-3">
            <h1>Subscription Orders</h1>
            @if($pendingCount > 0)
                <span class="badge badge-warning">{{ $pendingCount }} pending</span>
            @endif
        </div>
        <p>Review and process subscription payment requests</p>
    </div>

    <!-- Status Summary -->
    @php
        $approvedCount = \App\Models\SubscriptionOrder::approved()->count();
        $rejectedCount = \App\Models\SubscriptionOrder::rejected()->count();
    @endphp
    <div class="grid grid-cols-3 gap-3">
        <div class="card p-3">
            <div class="flex items-center gap-2">
                <div class="w-7 h-7 rounded-lg flex items-center justify-center" style="background-color: var(--warning-muted);">
                    <svg class="w-3.5 h-3.5" style="color: var(--warning);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-[11px]" style="color: var(--text-muted);">Pending</p>
                    <p class="text-lg font-bold" style="color: var(--warning);">{{ $pendingCount }}</p>
                </div>
            </div>
        </div>
        <div class="card p-3">
            <div class="flex items-center gap-2">
                <div class="w-7 h-7 rounded-lg flex items-center justify-center" style="background-color: var(--success-muted);">
                    <svg class="w-3.5 h-3.5" style="color: var(--success);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-[11px]" style="color: var(--text-muted);">Approved</p>
                    <p class="text-lg font-bold" style="color: var(--success);">{{ $approvedCount }}</p>
                </div>
            </div>
        </div>
        <div class="card p-3">
            <div class="flex items-center gap-2">
                <div class="w-7 h-7 rounded-lg flex items-center justify-center" style="background-color: var(--danger-muted);">
                    <svg class="w-3.5 h-3.5" style="color: var(--danger);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-[11px]" style="color: var(--text-muted);">Rejected</p>
                    <p class="text-lg font-bold" style="color: var(--danger);">{{ $rejectedCount }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card">
        <div class="flex flex-col sm:flex-row gap-3">
            <div class="flex-1 relative">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4" style="color: var(--text-muted);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <input 
                    type="text" 
                    wire:model.live.debounce.300ms="search"
                    placeholder="Search by user name or email..."
                    class="input pl-10"
                >
            </div>
            <select wire:model.live="statusFilter" class="input sm:w-40">
                <option value="">All Status</option>
                <option value="pending">Pending</option>
                <option value="approved">Approved</option>
                <option value="rejected">Rejected</option>
            </select>
        </div>
    </div>

    <!-- Orders Table -->
    <div class="card overflow-hidden p-0">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="table-header">
                    <tr>
                        <th class="px-4 py-2.5 text-left text-xs font-medium" style="color: var(--text-muted);">User</th>
                        <th class="px-4 py-2.5 text-left text-xs font-medium" style="color: var(--text-muted);">Plan</th>
                        <th class="px-4 py-2.5 text-left text-xs font-medium hidden md:table-cell" style="color: var(--text-muted);">Payment</th>
                        <th class="px-4 py-2.5 text-left text-xs font-medium hidden lg:table-cell" style="color: var(--text-muted);">Date</th>
                        <th class="px-4 py-2.5 text-left text-xs font-medium" style="color: var(--text-muted);">Status</th>
                        <th class="px-4 py-2.5 text-right text-xs font-medium" style="color: var(--text-muted);">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($orders as $order)
                        <tr class="table-row">
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-3">
                                    <div class="avatar-sm flex-shrink-0">
                                        {{ strtoupper(substr($order->user->name, 0, 1)) }}
                                    </div>
                                    <div class="min-w-0">
                                        <p class="text-sm font-medium truncate" style="color: var(--text-primary);">{{ $order->user->name }}</p>
                                        <p class="text-xs truncate" style="color: var(--text-muted);">{{ $order->user->email }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                <p class="text-sm font-medium" style="color: var(--text-primary);">{{ $order->subscriptionPlan->name }}</p>
                                <p class="text-xs" style="color: var(--accent);">{{ $order->subscriptionPlan->formatted_price }}</p>
                            </td>
                            <td class="px-4 py-3 hidden md:table-cell">
                                <p class="text-sm" style="color: var(--text-secondary);">{{ $order->paymentMethod->name }}</p>
                            </td>
                            <td class="px-4 py-3 hidden lg:table-cell">
                                <p class="text-sm" style="color: var(--text-secondary);">{{ $order->created_at->format('d M Y') }}</p>
                                <p class="text-[11px]" style="color: var(--text-muted);">{{ $order->created_at->format('H:i') }}</p>
                            </td>
                            <td class="px-4 py-3">
                                <span class="badge badge-{{ $order->status_color }}">{{ $order->status_label }}</span>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <button 
                                    wire:click="viewOrder({{ $order->id }})"
                                    class="btn-ghost text-xs"
                                >
                                    View Details
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-12 text-center">
                                <div class="empty-state">
                                    <div class="empty-state-icon">
                                        <svg class="w-6 h-6" style="color: var(--text-muted);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                        </svg>
                                    </div>
                                    <h3>No orders found</h3>
                                    <p>No subscription orders match your criteria</p>
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

    <!-- Order Details Modal -->
    @if($showModal && $viewingOrder)
        <div class="modal-overlay">
            <div class="modal-backdrop" wire:click="closeModal"></div>
            
            <div class="modal-content max-w-lg">
                <div class="flex items-center justify-between mb-5">
                    <div>
                        <h2 class="text-base font-semibold" style="color: var(--text-primary);">Order Details</h2>
                        <p class="text-[11px] mt-0.5" style="color: var(--text-muted);">ID: #{{ $viewingOrder->id }}</p>
                    </div>
                    <span class="badge badge-{{ $viewingOrder->status_color }}">{{ $viewingOrder->status_label }}</span>
                </div>

                <div class="space-y-4">
                    <!-- User Info -->
                    <div class="p-4 rounded-xl" style="background-color: var(--bg-muted);">
                        <p class="text-[11px] font-semibold uppercase tracking-wide mb-3" style="color: var(--text-muted);">User Information</p>
                        <div class="flex items-center gap-3">
                            <div class="avatar-sm flex-shrink-0">
                                {{ strtoupper(substr($viewingOrder->user->name, 0, 1)) }}
                            </div>
                            <div>
                                <p class="text-sm font-medium" style="color: var(--text-primary);">{{ $viewingOrder->user->name }}</p>
                                <p class="text-xs" style="color: var(--text-muted);">{{ $viewingOrder->user->email }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Plan & Payment -->
                    <div class="grid grid-cols-2 gap-3">
                        <div class="p-4 rounded-xl" style="background-color: var(--bg-muted);">
                            <p class="text-[11px] font-semibold uppercase tracking-wide mb-2" style="color: var(--text-muted);">Plan</p>
                            <p class="text-sm font-medium" style="color: var(--text-primary);">{{ $viewingOrder->subscriptionPlan->name }}</p>
                            <p class="text-lg font-bold mt-1" style="color: var(--accent);">{{ $viewingOrder->subscriptionPlan->formatted_price }}</p>
                            <p class="text-[11px] mt-1" style="color: var(--text-muted);">{{ $viewingOrder->subscriptionPlan->duration_days }} days</p>
                        </div>
                        <div class="p-4 rounded-xl" style="background-color: var(--bg-muted);">
                            <p class="text-[11px] font-semibold uppercase tracking-wide mb-2" style="color: var(--text-muted);">Payment Method</p>
                            <p class="text-sm font-medium" style="color: var(--text-primary);">{{ $viewingOrder->paymentMethod->name }}</p>
                            <p class="text-xs font-mono mt-1" style="color: var(--text-muted);">{{ $viewingOrder->paymentMethod->account_number }}</p>
                            <p class="text-[11px] mt-1" style="color: var(--text-muted);">{{ $viewingOrder->paymentMethod->account_holder_name }}</p>
                        </div>
                    </div>

                    <!-- Proof of Payment -->
                    @if($viewingOrder->proof_of_payment)
                        <div class="p-4 rounded-xl" style="background-color: var(--bg-muted);">
                            <p class="text-[11px] font-semibold uppercase tracking-wide mb-3" style="color: var(--text-muted);">Proof of Payment</p>
                            <a href="{{ Storage::url($viewingOrder->proof_of_payment) }}" target="_blank" class="block rounded-lg overflow-hidden" style="border: 1px solid var(--border-color);">
                                <img src="{{ Storage::url($viewingOrder->proof_of_payment) }}" alt="Proof of Payment" class="w-full max-h-48 object-contain" style="background-color: var(--bg-card);">
                            </a>
                        </div>
                    @endif

                    <!-- User Notes -->
                    @if($viewingOrder->notes)
                        <div class="p-4 rounded-xl" style="background-color: var(--bg-muted);">
                            <p class="text-[11px] font-semibold uppercase tracking-wide mb-2" style="color: var(--text-muted);">User Notes</p>
                            <p class="text-sm leading-relaxed" style="color: var(--text-primary);">{{ $viewingOrder->notes }}</p>
                        </div>
                    @endif

                    <!-- Pending Actions -->
                    @if($viewingOrder->isPending())
                        <div>
                            <label class="label">Admin Notes (optional)</label>
                            <textarea wire:model="adminNotes" class="input" rows="2" placeholder="Add internal notes about this order..."></textarea>
                        </div>

                        <div class="flex gap-3 pt-2">
                            <button 
                                wire:click="rejectOrder"
                                wire:confirm="Are you sure you want to reject this order?"
                                class="flex-1 py-2.5 rounded-lg text-sm font-medium transition-colors"
                                style="background-color: var(--danger-muted); color: var(--danger);"
                            >
                                Reject Order
                            </button>
                            <button 
                                wire:click="approveOrder"
                                class="flex-1 py-2.5 rounded-lg text-sm font-medium text-white transition-colors"
                                style="background-color: var(--success);"
                            >
                                Approve Order
                            </button>
                        </div>
                    @else
                        <!-- Processed Info -->
                        @if($viewingOrder->isApproved())
                            <div class="p-4 rounded-xl border" style="border-color: rgba(16,185,129,0.2); background-color: rgba(16,185,129,0.05);">
                        @else
                            <div class="p-4 rounded-xl border" style="border-color: rgba(239,68,68,0.2); background-color: rgba(239,68,68,0.05);">
                        @endif
                            <p class="text-[11px] font-semibold uppercase tracking-wide mb-2" style="color: var(--text-muted);">Processed</p>
                            <div class="flex items-center gap-2">
                                <div class="w-6 h-6 rounded-full flex items-center justify-center flex-shrink-0" style="background-color: var(--bg-card);">
                                    <svg class="w-3.5 h-3.5" style="color: var(--text-secondary);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-sm font-medium" style="color: var(--text-primary);">
                                        {{ $viewingOrder->status_label }} by {{ $viewingOrder->processedByUser?->name ?? 'Unknown' }}
                                    </p>
                                    <p class="text-[11px]" style="color: var(--text-muted);">
                                        {{ $viewingOrder->processed_at?->format('d F Y, H:i') }}
                                    </p>
                                </div>
                            </div>
                            @if($viewingOrder->admin_notes)
                                <div class="mt-3 pt-3" style="border-top: 1px solid var(--border-color);">
                                    <p class="text-xs" style="color: var(--text-secondary);">{{ $viewingOrder->admin_notes }}</p>
                                </div>
                            @endif
                        </div>

                        <button wire:click="closeModal" class="btn-secondary w-full">Close</button>
                    @endif
                </div>
            </div>
        </div>
    @endif
</div>
