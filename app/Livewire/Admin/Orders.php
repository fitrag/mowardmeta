<?php

namespace App\Livewire\Admin;

use App\Models\SubscriptionOrder;
use Carbon\Carbon;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
#[Title('Subscription Orders')]
class Orders extends Component
{
    use WithPagination;

    #[Url(except: '')]
    public string $search = '';
    
    #[Url(except: '')]
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
        $this->viewingOrder = SubscriptionOrder::with(['user:id,name,email', 'subscriptionPlan:id,name,price,duration_days', 'paymentMethod:id,name', 'processedByUser:id,name'])->find($orderId);
        $this->viewingOrderId = $orderId;
        $this->adminNotes = $this->viewingOrder->admin_notes ?? '';
        $this->showModal = true;
    }

    public function approveOrder(): void
    {
        if (!$this->viewingOrder || !$this->viewingOrder->isPending()) {
            session()->flash('error', 'Order not found or already processed.');
            return;
        }

        $order = $this->viewingOrder;
        $user = $order->user;
        $plan = $order->subscriptionPlan;

        if (!$user || !$plan) {
            session()->flash('error', 'User or subscription plan not found.');
            return;
        }

        // Calculate new expiry date
        $baseDate = $user->subscription_expires_at && $user->subscription_expires_at->isFuture()
            ? $user->subscription_expires_at->copy()
            : Carbon::now();

        $newExpiryDate = $baseDate->addDays($plan->duration_days);

        // Update user subscription
        $user->update([
            'is_subscribed' => true,
            'subscription_expires_at' => $newExpiryDate,
        ]);

        // Update order status
        $order->update([
            'status' => 'approved',
            'admin_notes' => $this->adminNotes ?: null,
            'processed_at' => now(),
            'processed_by' => auth()->id(),
        ]);

        session()->flash('success', 'Order approved successfully!');
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

    #[Computed]
    public function orders()
    {
        return SubscriptionOrder::with(['user:id,name,email', 'subscriptionPlan:id,name,price', 'paymentMethod:id,name'])
            ->select(['id', 'user_id', 'subscription_plan_id', 'payment_method_id', 'status', 'created_at', 'processed_at'])
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
    }

    #[Computed]
    public function pendingCount(): int
    {
        return SubscriptionOrder::pending()->count();
    }

    public function render()
    {
        return view('livewire.admin.orders', [
            'orders' => $this->orders,
            'pendingCount' => $this->pendingCount,
        ]);
    }
}
