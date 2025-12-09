<?php

namespace App\Livewire\User;

use App\Models\PaymentMethod;
use App\Models\SubscriptionOrder;
use App\Models\SubscriptionPlan;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('components.layouts.app')]
#[Title('Subscription')]
class Subscription extends Component
{
    use WithFileUploads;

    public bool $showOrderModal = false;
    public ?int $selectedPlanId = null;
    public ?SubscriptionPlan $selectedPlan = null;

    #[Validate('required|exists:payment_methods,id')]
    public ?int $selectedPaymentMethodId = null;

    #[Validate('nullable|image|max:2048')]
    public $proofOfPayment = null;

    #[Validate('nullable|max:500')]
    public string $notes = '';

    public function selectPlan(int $planId): void
    {
        $user = auth()->user();
        
        // Check if user already has a pending order
        if ($user->hasPendingOrder()) {
            session()->flash('error', 'You already have a pending order. Please wait for it to be processed.');
            return;
        }

        $this->selectedPlan = SubscriptionPlan::find($planId);
        $this->selectedPlanId = $planId;
        $this->selectedPaymentMethodId = null;
        $this->proofOfPayment = null;
        $this->notes = '';
        $this->showOrderModal = true;
    }

    public function submitOrder(): void
    {
        $user = auth()->user();

        // Check again for pending order
        if ($user->hasPendingOrder()) {
            session()->flash('error', 'You already have a pending order.');
            $this->closeModal();
            return;
        }

        $this->validate();

        // Upload proof of payment if provided
        $proofPath = null;
        if ($this->proofOfPayment) {
            $proofPath = $this->proofOfPayment->store('proofs', 'public');
        }

        // Create the order
        SubscriptionOrder::create([
            'user_id' => $user->id,
            'subscription_plan_id' => $this->selectedPlanId,
            'payment_method_id' => $this->selectedPaymentMethodId,
            'status' => 'pending',
            'proof_of_payment' => $proofPath,
            'notes' => $this->notes ?: null,
        ]);

        $this->closeModal();
        session()->flash('success', 'Order submitted successfully! Please wait for admin approval.');
    }

    public function cancelOrder(int $orderId): void
    {
        $order = SubscriptionOrder::where('user_id', auth()->id())
            ->where('id', $orderId)
            ->pending()
            ->first();

        if ($order) {
            $order->delete();
            session()->flash('success', 'Order cancelled.');
        }
    }

    public function closeModal(): void
    {
        $this->showOrderModal = false;
        $this->selectedPlan = null;
        $this->selectedPlanId = null;
        $this->selectedPaymentMethodId = null;
        $this->proofOfPayment = null;
        $this->notes = '';
        $this->resetValidation();
    }

    public function render()
    {
        $user = auth()->user();
        $plans = SubscriptionPlan::getActive();
        $paymentMethods = PaymentMethod::getActive();
        $pendingOrder = $user->latestPendingOrder();
        $recentOrders = $user->subscriptionOrders()->latest()->take(5)->get();
        
        return view('livewire.user.subscription', [
            'user' => $user,
            'plans' => $plans,
            'paymentMethods' => $paymentMethods,
            'isSubscribed' => $user->isSubscribed(),
            'remainingGenerations' => $user->getRemainingGenerations(),
            'dailyLimit' => $user->getDailyLimit(),
            'todayCount' => $user->getTodayGenerationCount(),
            'hasPendingOrder' => $user->hasPendingOrder(),
            'pendingOrder' => $pendingOrder,
            'recentOrders' => $recentOrders,
        ]);
    }
}
