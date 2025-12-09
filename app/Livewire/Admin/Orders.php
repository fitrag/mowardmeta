<?php

namespace App\Livewire\Admin;

use App\Models\SubscriptionOrder;
use Carbon\Carbon;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
#[Title('Subscription Orders')]
class Orders extends Component
{
    use WithPagination;

    public string $search = '';
    public string $statusFilter = '';
    
    public bool $showModal = false;
    public ?int $viewingOrderId = null;
    public ?SubscriptionOrder $viewingOrder = null;
    public string $adminNotes = '';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedStatusFilter(): void
    {
        $this->resetPage();
    }

    public function viewOrder(int $orderId): void
    {
        $this->viewingOrder = SubscriptionOrder::with(['user', 'subscriptionPlan', 'paymentMethod', 'processedByUser'])->find($orderId);
        $this->viewingOrderId = $orderId;
        $this->adminNotes = $this->viewingOrder->admin_notes ?? '';
        $this->showModal = true;
    }

    public function approveOrder(): void
    {
        if (!$this->viewingOrder || !$this->viewingOrder->isPending()) {
            return;
        }

        $order = $this->viewingOrder;
        $user = $order->user;
        $plan = $order->subscriptionPlan;

        // Calculate new expiry date
        $baseDate = $user->subscription_expires_at && $user->subscription_expires_at->isFuture()
            ? $user->subscription_expires_at
            : Carbon::now();

        // Update user subscription
        $user->update([
            'is_subscribed' => true,
            'subscription_expires_at' => $baseDate->addDays($plan->duration_days),
        ]);

        // Update order status
        $order->update([
            'status' => 'approved',
            'admin_notes' => $this->adminNotes ?: null,
            'processed_at' => now(),
            'processed_by' => auth()->id(),
        ]);

        $this->closeModal();
    }

    public function rejectOrder(): void
    {
        if (!$this->viewingOrder || !$this->viewingOrder->isPending()) {
            return;
        }

        $this->viewingOrder->update([
            'status' => 'rejected',
            'admin_notes' => $this->adminNotes ?: 'Order rejected',
            'processed_at' => now(),
            'processed_by' => auth()->id(),
        ]);

        $this->closeModal();
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->viewingOrder = null;
        $this->viewingOrderId = null;
        $this->adminNotes = '';
    }

    public function render()
    {
        $orders = SubscriptionOrder::with(['user', 'subscriptionPlan', 'paymentMethod'])
            ->when($this->search, function ($query) {
                $query->whereHas('user', function ($q) {
                    $q->where('name', 'like', "%{$this->search}%")
                      ->orWhere('email', 'like', "%{$this->search}%");
                });
            })
            ->when($this->statusFilter, function ($query) {
                $query->where('status', $this->statusFilter);
            })
            ->latest()
            ->paginate(10);

        $pendingCount = SubscriptionOrder::pending()->count();

        return view('livewire.admin.orders', [
            'orders' => $orders,
            'pendingCount' => $pendingCount,
        ]);
    }
}
