<?php

namespace App\Livewire\Admin;

use App\Models\License;
use App\Models\ProductOrder;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
#[Title('Product Orders')]
class ProductOrders extends Component
{
    use WithPagination;

    public string $search = '';
    public string $statusFilter = '';
    public bool $showDetailModal = false;
    public ?ProductOrder $selectedOrder = null;
    public string $adminNotes = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingStatusFilter(): void
    {
        $this->resetPage();
    }

    public function viewOrder(int $id): void
    {
        $this->selectedOrder = ProductOrder::with(['user', 'product', 'paymentMethod', 'license'])->findOrFail($id);
        $this->adminNotes = $this->selectedOrder->admin_notes ?? '';
        $this->showDetailModal = true;
    }

    public function approve(): void
    {
        if (!$this->selectedOrder || !$this->selectedOrder->isPending()) {
            return;
        }

        $product = $this->selectedOrder->product;
        $licenseId = null;

        // Create license if product requires it
        if ($product->requires_license) {
            $license = License::create([
                'user_id' => $this->selectedOrder->user_id,
                'product_name' => $product->name,
                'status' => 'active',
                'activated_at' => now(),
                'expires_at' => $product->license_duration_days 
                    ? now()->addDays($product->license_duration_days) 
                    : null,
                'max_activations' => 1,
            ]);
            $licenseId = $license->id;
        }

        $this->selectedOrder->update([
            'status' => 'approved',
            'license_id' => $licenseId,
            'admin_notes' => $this->adminNotes ?: null,
            'processed_at' => now(),
            'processed_by' => auth()->id(),
        ]);

        $this->closeModal();
    }

    public function reject(): void
    {
        if (!$this->selectedOrder || !$this->selectedOrder->isPending()) {
            return;
        }

        $this->selectedOrder->update([
            'status' => 'rejected',
            'admin_notes' => $this->adminNotes ?: null,
            'processed_at' => now(),
            'processed_by' => auth()->id(),
        ]);

        $this->closeModal();
    }

    public function closeModal(): void
    {
        $this->showDetailModal = false;
        $this->selectedOrder = null;
        $this->adminNotes = '';
    }

    public function render()
    {
        $query = ProductOrder::with(['user', 'product', 'paymentMethod'])
            ->when($this->search, function ($q) {
                $q->whereHas('user', fn($q) => $q->where('name', 'like', "%{$this->search}%")
                    ->orWhere('email', 'like', "%{$this->search}%"))
                    ->orWhereHas('product', fn($q) => $q->where('name', 'like', "%{$this->search}%"));
            })
            ->when($this->statusFilter, fn($q) => $q->where('status', $this->statusFilter))
            ->latest();

        return view('livewire.admin.product-orders', [
            'orders' => $query->paginate(15),
            'stats' => [
                'pending' => ProductOrder::pending()->count(),
                'approved' => ProductOrder::approved()->count(),
                'total' => ProductOrder::count(),
            ],
        ]);
    }
}
