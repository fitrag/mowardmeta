<div class="space-y-6">
    <div>
        <h1 class="text-2xl font-bold" style="color: var(--text-primary);">Product Orders</h1>
        <p class="mt-1 text-sm" style="color: var(--text-secondary);">Review and process product purchase orders</p>
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="card">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-amber-500/20 flex items-center justify-center"><svg class="w-5 h-5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg></div>
                <div><p class="text-2xl font-bold" style="color: var(--text-primary);">{{ $stats['pending'] }}</p><p class="text-sm" style="color: var(--text-secondary);">Pending</p></div>
            </div>
        </div>
        <div class="card">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-emerald-500/20 flex items-center justify-center"><svg class="w-5 h-5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg></div>
                <div><p class="text-2xl font-bold" style="color: var(--text-primary);">{{ $stats['approved'] }}</p><p class="text-sm" style="color: var(--text-secondary);">Approved</p></div>
            </div>
        </div>
        <div class="card">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-primary-500/20 flex items-center justify-center"><svg class="w-5 h-5 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg></div>
                <div><p class="text-2xl font-bold" style="color: var(--text-primary);">{{ $stats['total'] }}</p><p class="text-sm" style="color: var(--text-secondary);">Total</p></div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card">
        <div class="flex flex-col sm:flex-row gap-4">
            <input type="text" wire:model.live.debounce.300ms="search" placeholder="Search..." class="input flex-1">
            <select wire:model.live="statusFilter" class="input w-full sm:w-48">
                <option value="">All Status</option>
                <option value="pending">Pending</option>
                <option value="approved">Approved</option>
                <option value="rejected">Rejected</option>
            </select>
        </div>
    </div>

    <!-- Table -->
    <div class="card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr style="border-bottom: 1px solid var(--border-color);">
                        <th class="text-left p-4 text-sm font-medium" style="color: var(--text-secondary);">User</th>
                        <th class="text-left p-4 text-sm font-medium" style="color: var(--text-secondary);">Product</th>
                        <th class="text-left p-4 text-sm font-medium" style="color: var(--text-secondary);">Price</th>
                        <th class="text-left p-4 text-sm font-medium" style="color: var(--text-secondary);">Status</th>
                        <th class="text-left p-4 text-sm font-medium" style="color: var(--text-secondary);">Date</th>
                        <th class="text-right p-4 text-sm font-medium" style="color: var(--text-secondary);">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($orders as $order)
                        <tr style="border-bottom: 1px solid var(--border-color);" class="hover:bg-[var(--bg-hover)]">
                            <td class="p-4">
                                <p class="font-medium" style="color: var(--text-primary);">{{ $order->user->name }}</p>
                                <p class="text-sm" style="color: var(--text-secondary);">{{ $order->user->email }}</p>
                            </td>
                            <td class="p-4" style="color: var(--text-primary);">{{ $order->product->name }}</td>
                            <td class="p-4 text-primary-500 font-medium">{{ $order->formatted_price }}</td>
                            <td class="p-4"><span class="px-2 py-1 text-xs rounded-full bg-{{ $order->status_color }}-500/20 text-{{ $order->status_color }}-500">{{ $order->status_label }}</span></td>
                            <td class="p-4 text-sm" style="color: var(--text-secondary);">{{ $order->created_at->format('d M Y H:i') }}</td>
                            <td class="p-4 text-right"><button wire:click="viewOrder({{ $order->id }})" class="btn-secondary text-sm">View</button></td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="p-8 text-center" style="color: var(--text-secondary);">No orders found</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4">{{ $orders->links() }}</div>
    </div>

    <!-- Detail Modal -->
    @if($showDetailModal && $selectedOrder)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-modal="true">
            <div class="flex items-center justify-center min-h-screen p-4">
                <div class="fixed inset-0 bg-black/50" wire:click="closeModal"></div>
                <div class="relative w-full max-w-lg card">
                    <h2 class="text-xl font-bold mb-6" style="color: var(--text-primary);">Order Details</h2>
                    <div class="space-y-4">
                        <div class="grid grid-cols-2 gap-4">
                            <div><p class="text-sm" style="color: var(--text-secondary);">User</p><p class="font-medium" style="color: var(--text-primary);">{{ $selectedOrder->user->name }}</p></div>
                            <div><p class="text-sm" style="color: var(--text-secondary);">Email</p><p class="font-medium" style="color: var(--text-primary);">{{ $selectedOrder->user->email }}</p></div>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div><p class="text-sm" style="color: var(--text-secondary);">Product</p><p class="font-medium" style="color: var(--text-primary);">{{ $selectedOrder->product->name }}</p></div>
                            <div><p class="text-sm" style="color: var(--text-secondary);">Price</p><p class="font-medium text-primary-500">{{ $selectedOrder->formatted_price }}</p></div>
                        </div>
                        <div><p class="text-sm" style="color: var(--text-secondary);">Payment Method</p><p class="font-medium" style="color: var(--text-primary);">{{ $selectedOrder->paymentMethod?->name ?? '-' }}</p></div>
                        @if($selectedOrder->proof_of_payment)
                            <div><p class="text-sm mb-2" style="color: var(--text-secondary);">Proof of Payment</p><a href="{{ Storage::url($selectedOrder->proof_of_payment) }}" target="_blank"><img src="{{ Storage::url($selectedOrder->proof_of_payment) }}" class="max-h-48 rounded-lg border" style="border-color: var(--border-color);"></a></div>
                        @endif
                        @if($selectedOrder->notes)
                            <div><p class="text-sm" style="color: var(--text-secondary);">Notes</p><p style="color: var(--text-primary);">{{ $selectedOrder->notes }}</p></div>
                        @endif
                        @if($selectedOrder->isPending())
                            <div><label class="block text-sm font-medium mb-1" style="color: var(--text-secondary);">Admin Notes</label><textarea wire:model="adminNotes" class="input w-full" rows="2"></textarea></div>
                            <div class="flex justify-end gap-3 pt-4">
                                <button wire:click="closeModal" class="btn-secondary">Cancel</button>
                                <button wire:click="reject" wire:confirm="Reject?" class="px-4 py-2 bg-red-500 hover:bg-red-600 text-white rounded-lg">Reject</button>
                                <button wire:click="approve" class="btn-primary">Approve</button>
                            </div>
                        @else
                            <div class="pt-4"><span class="px-3 py-1 text-sm rounded-full bg-{{ $selectedOrder->status_color }}-500/20 text-{{ $selectedOrder->status_color }}-500">{{ $selectedOrder->status_label }}</span></div>
                            <div class="flex justify-end pt-4"><button wire:click="closeModal" class="btn-secondary">Close</button></div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
