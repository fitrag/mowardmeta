<div class="space-y-8">
    <div>
        <h1 class="text-2xl font-bold" style="color: var(--text-primary);">Product Store</h1>
        <p class="mt-1 text-sm" style="color: var(--text-secondary);">Browse and download digital products</p>
    </div>

    @if(session('success'))<div class="p-4 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-500">{{ session('success') }}</div>@endif
    @if(session('error'))<div class="p-4 rounded-xl bg-red-500/10 border border-red-500/20 text-red-500">{{ session('error') }}</div>@endif
    @if(session('info'))<div class="p-4 rounded-xl bg-blue-500/10 border border-blue-500/20 text-blue-500">{{ session('info') }}</div>@endif

    <!-- My Products -->
    @if($myProducts->count() > 0)
        <div class="card">
            <h2 class="text-lg font-bold mb-4" style="color: var(--text-primary);">My Products</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($myProducts as $order)
                    <div class="p-4 rounded-xl flex items-center gap-4" style="background-color: var(--bg-hover);">
                        @if($order->product->thumbnail)
                            <img src="{{ asset($order->product->thumbnail) }}" class="w-16 h-16 rounded-lg object-cover">
                        @else
                            <div class="w-16 h-16 rounded-lg flex items-center justify-center" style="background-color: var(--bg-card);"><svg class="w-8 h-8" style="color: var(--text-muted);" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg></div>
                        @endif
                        <div class="flex-1 min-w-0">
                            <p class="font-medium truncate" style="color: var(--text-primary);">{{ $order->product->name }}</p>
                            <p class="text-xs" style="color: var(--text-muted);">v{{ $order->product->version }}</p>
                        </div>
                        @if($order->product->file_path)
                            <button wire:click="downloadProduct({{ $order->product->id }})" class="btn-primary text-sm">Download</button>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <!-- Pending Orders -->
    @if($pendingOrders->count() > 0)
        <div class="card border-amber-500/30">
            <h2 class="text-lg font-bold mb-4 text-amber-500">Pending Orders</h2>
            <div class="space-y-3">
                @foreach($pendingOrders as $order)
                    <div class="flex items-center justify-between p-4 rounded-xl" style="background-color: var(--bg-hover);">
                        <div>
                            <p class="font-medium" style="color: var(--text-primary);">{{ $order->product->name }}</p>
                            <p class="text-sm text-primary-500">{{ $order->formatted_price }}</p>
                        </div>
                        <button wire:click="cancelOrder({{ $order->id }})" wire:confirm="Cancel?" class="text-red-500 text-sm">Cancel</button>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <!-- Filters -->
    <div class="card">
        <div class="flex flex-col sm:flex-row gap-4">
            <input type="text" wire:model.live.debounce.300ms="search" placeholder="Search products..." class="input flex-1">
            <select wire:model.live="typeFilter" class="input w-full sm:w-40">
                <option value="">All</option>
                <option value="free">Free</option>
                <option value="paid">Paid</option>
            </select>
        </div>
    </div>

    <!-- Products Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($products as $product)
            <div class="card group hover:border-primary-500/30 transition-all">
                @if($product->thumbnail)
                    <img src="{{ asset($product->thumbnail) }}" alt="{{ $product->name }}" class="w-full h-44 object-cover rounded-lg mb-4 group-hover:scale-[1.02] transition-transform">
                @else
                    <div class="w-full h-44 rounded-lg mb-4 flex items-center justify-center" style="background-color: var(--bg-hover);"><svg class="w-16 h-16" style="color: var(--text-muted);" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg></div>
                @endif

                <div class="flex items-start justify-between mb-2">
                    <h3 class="font-bold" style="color: var(--text-primary);">{{ $product->name }}</h3>
                    @if($product->is_featured)<span class="px-2 py-0.5 text-xs rounded bg-amber-500/20 text-amber-500">Featured</span>@endif
                </div>

                <p class="text-sm mb-4 line-clamp-2" style="color: var(--text-secondary);">{{ $product->short_description }}</p>

                <div class="flex items-center gap-2 mb-4">
                    @if($product->hasDiscount())
                        <span class="text-xl font-bold text-emerald-500">{{ $product->formatted_price }}</span>
                        <span class="text-sm line-through" style="color: var(--text-muted);">{{ $product->formatted_original_price }}</span>
                        <span class="px-1.5 py-0.5 text-xs rounded bg-red-500/20 text-red-500">-{{ $product->getDiscountPercentage() }}%</span>
                    @else
                        <span class="text-xl font-bold {{ $product->isFree() ? 'text-cyan-500' : 'text-primary-500' }}">{{ $product->formatted_price }}</span>
                    @endif
                </div>

                <div class="flex items-center gap-3 text-xs mb-4" style="color: var(--text-muted);">
                    <span>v{{ $product->version }}</span>
                    <span>{{ $product->download_count }} downloads</span>
                    @if($product->requires_license)<span class="px-1.5 py-0.5 rounded bg-purple-500/20 text-purple-500">License</span>@endif
                </div>

                <div class="flex gap-2">
                    <button wire:click="viewProduct({{ $product->id }})" class="flex-1 btn-secondary text-sm">Details</button>
                    @if(in_array($product->id, $purchasedIds))
                        @if($product->file_path)
                            <button wire:click="downloadProduct({{ $product->id }})" class="flex-1 btn-primary text-sm">Download</button>
                        @else
                            <span class="flex-1 text-center py-2 text-sm text-emerald-500">Owned</span>
                        @endif
                    @elseif($product->userHasPendingOrder(auth()->user()))
                        <span class="flex-1 text-center py-2 text-sm text-amber-500">Pending</span>
                    @else
                        <button wire:click="buyProduct({{ $product->id }})" class="flex-1 btn-primary text-sm">{{ $product->isFree() ? 'Get Free' : 'Buy Now' }}</button>
                    @endif
                </div>
            </div>
        @empty
            <div class="col-span-full text-center py-12"><p style="color: var(--text-secondary);">No products available</p></div>
        @endforelse
    </div>

    <div>{{ $products->links() }}</div>

    <!-- Detail Modal -->
    @if($showDetailModal && $selectedProduct)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-modal="true">
            <div class="flex items-start justify-center min-h-screen p-4 pt-20">
                <div class="fixed inset-0 bg-black/50" wire:click="closeModal"></div>
                <div class="relative w-full max-w-2xl card max-h-[80vh] overflow-y-auto">
                    @if($selectedProduct->thumbnail)<img src="{{ asset($selectedProduct->thumbnail) }}" class="w-full h-56 object-cover rounded-lg mb-6">@endif
                    <h2 class="text-2xl font-bold mb-2" style="color: var(--text-primary);">{{ $selectedProduct->name }}</h2>
                    <div class="flex items-center gap-3 mb-4">
                        @if($selectedProduct->hasDiscount())
                            <span class="text-2xl font-bold text-emerald-500">{{ $selectedProduct->formatted_price }}</span>
                            <span class="text-lg line-through" style="color: var(--text-muted);">{{ $selectedProduct->formatted_original_price }}</span>
                        @else
                            <span class="text-2xl font-bold {{ $selectedProduct->isFree() ? 'text-cyan-500' : 'text-primary-500' }}">{{ $selectedProduct->formatted_price }}</span>
                        @endif
                        <span class="text-sm" style="color: var(--text-muted);">v{{ $selectedProduct->version }}</span>
                    </div>
                    @if($selectedProduct->description)<div class="prose prose-sm max-w-none mb-6" style="color: var(--text-secondary);">{!! nl2br(e($selectedProduct->description)) !!}</div>@endif
                    @if($selectedProduct->features)
                        <div class="mb-6">
                            <h3 class="font-bold mb-2" style="color: var(--text-primary);">Features</h3>
                            <ul class="space-y-1">@foreach($selectedProduct->features as $f)<li class="flex items-center gap-2 text-sm" style="color: var(--text-secondary);"><svg class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>{{ $f }}</li>@endforeach</ul>
                        </div>
                    @endif
                    @if($selectedProduct->requirements)
                        <div class="mb-6">
                            <h3 class="font-bold mb-2" style="color: var(--text-primary);">Requirements</h3>
                            <ul class="space-y-1">@foreach($selectedProduct->requirements as $r)<li class="text-sm" style="color: var(--text-secondary);">• {{ $r }}</li>@endforeach</ul>
                        </div>
                    @endif
                    <div class="flex items-center gap-4 text-sm mb-6" style="color: var(--text-muted);">
                        <span>{{ $selectedProduct->download_count }} downloads</span>
                        @if($selectedProduct->file_size)<span>{{ $selectedProduct->formatted_file_size }}</span>@endif
                        @if($selectedProduct->demo_url)<a href="{{ $selectedProduct->demo_url }}" target="_blank" class="text-primary-500 hover:underline">Demo</a>@endif
                        @if($selectedProduct->documentation_url)<a href="{{ $selectedProduct->documentation_url }}" target="_blank" class="text-primary-500 hover:underline">Docs</a>@endif
                    </div>
                    <div class="flex gap-3">
                        <button wire:click="closeModal" class="btn-secondary">Close</button>
                        @if(in_array($selectedProduct->id, $purchasedIds))
                            @if($selectedProduct->file_path)<button wire:click="downloadProduct({{ $selectedProduct->id }})" class="btn-primary">Download</button>@else<span class="text-emerald-500 py-2">You own this</span>@endif
                        @elseif($selectedProduct->userHasPendingOrder(auth()->user()))
                            <span class="text-amber-500 py-2">Order Pending</span>
                        @else
                            <button wire:click="buyProduct({{ $selectedProduct->id }})" class="btn-primary">{{ $selectedProduct->isFree() ? 'Get Free' : 'Buy Now' }}</button>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Order Modal -->
    @if($showOrderModal && $selectedProduct)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-modal="true">
            <div class="flex items-center justify-center min-h-screen p-4">
                <div class="fixed inset-0 bg-black/50" wire:click="closeModal"></div>
                <div class="relative w-full max-w-md card">
                    <h2 class="text-xl font-bold mb-2" style="color: var(--text-primary);">Purchase Product</h2>
                    <p class="text-sm mb-6" style="color: var(--text-secondary);">{{ $selectedProduct->name }} - {{ $selectedProduct->formatted_price }}</p>
                    <form wire:submit="submitOrder" class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium mb-2" style="color: var(--text-secondary);">Payment Method</label>
                            <select wire:model="selectedPaymentMethodId" class="input w-full">
                                <option value="">Select...</option>
                                @foreach($paymentMethods as $m)<option value="{{ $m->id }}">{{ $m->name }}</option>@endforeach
                            </select>
                            @error('selectedPaymentMethodId')<span class="text-red-500 text-xs">{{ $message }}</span>@enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-2" style="color: var(--text-secondary);">Proof of Payment</label>
                            <input type="file" wire:model="proofOfPayment" accept="image/*" class="input w-full">
                            @if($proofOfPayment)<img src="{{ $proofOfPayment->temporaryUrl() }}" class="mt-2 max-h-32 rounded-lg">@endif
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-2" style="color: var(--text-secondary);">Notes (optional)</label>
                            <textarea wire:model="notes" class="input w-full" rows="2"></textarea>
                        </div>
                        <div class="flex justify-end gap-3 pt-4">
                            <button type="button" wire:click="closeModal" class="btn-secondary">Cancel</button>
                            <button type="submit" class="btn-primary">Submit Order</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
</div>
