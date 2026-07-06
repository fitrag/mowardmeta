<?php

namespace App\Livewire\User;

use App\Models\License;
use App\Models\PaymentMethod;
use App\Models\Product;
use App\Models\ProductDownload;
use App\Models\ProductOrder;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Illuminate\Support\Facades\Storage;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
#[Title('Product Store')]
class ProductStore extends Component
{
    use WithPagination, WithFileUploads;

    public string $search = '';
    public string $typeFilter = '';
    public bool $showOrderModal = false;
    public bool $showDetailModal = false;
    public ?Product $selectedProduct = null;
    public string $selectedPaymentMethodId = '';
    public $proofOfPayment = null;
    public string $notes = '';

    protected function rules(): array
    {
        return [
            'selectedPaymentMethodId' => 'required|exists:payment_methods,id',
            'proofOfPayment' => 'nullable|image|max:2048',
            'notes' => 'nullable|string|max:500',
        ];
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function viewProduct(int $id): void
    {
        $this->selectedProduct = Product::findOrFail($id);
        $this->showDetailModal = true;
    }

    public function buyProduct(int $id): void
    {
        $this->selectedProduct = Product::findOrFail($id);
        
        // If free, process immediately
        if ($this->selectedProduct->isFree()) {
            $this->processFreePurchase();
            return;
        }

        $this->showOrderModal = true;
    }

    protected function processFreePurchase(): void
    {
        $user = auth()->user();
        
        // Check if already has order
        $existingOrder = ProductOrder::where('user_id', $user->id)
            ->where('product_id', $this->selectedProduct->id)
            ->where('status', 'approved')
            ->first();

        if ($existingOrder) {
            session()->flash('info', 'You already have access to this product.');
            $this->closeModal();
            return;
        }

        $licenseId = null;

        // Create license if required
        if ($this->selectedProduct->requires_license) {
            $license = License::create([
                'user_id' => $user->id,
                'product_name' => $this->selectedProduct->name,
                'status' => 'active',
                'activated_at' => now(),
                'expires_at' => $this->selectedProduct->license_duration_days 
                    ? now()->addDays($this->selectedProduct->license_duration_days) 
                    : null,
                'max_activations' => 1,
            ]);
            $licenseId = $license->id;
        }

        // Create approved order
        ProductOrder::create([
            'user_id' => $user->id,
            'product_id' => $this->selectedProduct->id,
            'price' => 0,
            'status' => 'approved',
            'license_id' => $licenseId,
            'processed_at' => now(),
        ]);

        session()->flash('success', 'Product added to your library!');
        $this->closeModal();
    }

    public function submitOrder(): void
    {
        $this->validate();

        $proofPath = null;
        if ($this->proofOfPayment) {
            $proofPath = $this->proofOfPayment->store('product-proofs', 'public');
        }

        ProductOrder::create([
            'user_id' => auth()->id(),
            'product_id' => $this->selectedProduct->id,
            'payment_method_id' => $this->selectedPaymentMethodId,
            'price' => $this->selectedProduct->getCurrentPrice(),
            'proof_of_payment' => $proofPath,
            'notes' => $this->notes ?: null,
            'status' => 'pending',
        ]);

        $this->closeModal();
        session()->flash('success', 'Order submitted! Please wait for admin approval.');
    }

    public function downloadProduct(int $id): mixed
    {
        $product = Product::findOrFail($id);
        $user = auth()->user();

        // Check access
        if (!$product->userHasPurchased($user)) {
            session()->flash('error', 'You do not have access to this product.');
            return null;
        }

        if (!$product->file_path || !Storage::disk('local')->exists($product->file_path)) {
            session()->flash('error', 'Product file not found.');
            return null;
        }

        // Log download
        ProductDownload::create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        // Increment download count
        $product->increment('download_count');

        return Storage::disk('local')->download(
            $product->file_path,
            $product->file_name ?? 'download'
        );
    }

    public function cancelOrder(int $orderId): void
    {
        $order = ProductOrder::where('user_id', auth()->id())
            ->where('id', $orderId)
            ->pending()
            ->firstOrFail();

        $order->delete();
        session()->flash('success', 'Order cancelled.');
    }

    public function closeModal(): void
    {
        $this->showOrderModal = false;
        $this->showDetailModal = false;
        $this->selectedProduct = null;
        $this->selectedPaymentMethodId = '';
        $this->proofOfPayment = null;
        $this->notes = '';
        $this->resetValidation();
    }

    public function render()
    {
        $user = auth()->user();

        $query = Product::active()
            ->when($this->search, fn($q) => $q->where('name', 'like', "%{$this->search}%"))
            ->when($this->typeFilter, fn($q) => $q->where('type', $this->typeFilter))
            ->ordered();

        // Get user's purchased products
        $purchasedIds = ProductOrder::where('user_id', $user->id)
            ->where('status', 'approved')
            ->pluck('product_id')
            ->toArray();

        return view('livewire.user.product-store', [
            'products' => $query->paginate(12),
            'paymentMethods' => PaymentMethod::where('is_active', true)->get(),
            'purchasedIds' => $purchasedIds,
            'pendingOrders' => ProductOrder::where('user_id', $user->id)->pending()->with('product')->get(),
            'myProducts' => ProductOrder::where('user_id', $user->id)
                ->where('status', 'approved')
                ->with('product')
                ->latest()
                ->get(),
        ]);
    }
}
