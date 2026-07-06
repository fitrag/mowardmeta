<?php

namespace App\Livewire\User;

use App\Models\SubscriptionOrder;
use App\Models\SubscriptionPlan;
use App\Services\PakasirService;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.app')]
#[Title('Subscription')]
class Subscription extends Component
{
    public bool $showPaymentModal = false;

    public bool $showPaymentSuccess = false;

    public ?int $selectedPlanId = null;

    public ?SubscriptionPlan $selectedPlan = null;

    public string $paymentQRIS = '';

    public string $paymentPaymentNumber = '';

    public ?string $pakasirOrderId = null;

    public ?int $totalPayment = null;

    public string $paymentPollingState = '';

    public function selectPlan(int $planId): void
    {
        $user = auth()->user();

        if ($user->isSubscribed()) {
            session()->flash('info', 'You already have an active subscription.');

            return;
        }

        if ($user->hasPendingOrder()) {
            session()->flash('error', 'You already have a pending order. Please wait for it to be processed.');

            return;
        }

        $this->selectedPlan = SubscriptionPlan::find($planId);
        $this->selectedPlanId = $planId;
        $this->showPaymentModal = true;
        $this->paymentQRIS = '';
        $this->paymentPaymentNumber = '';
        $this->pakasirOrderId = null;
        $this->totalPayment = null;
        $this->paymentPollingState = 'idle';
    }

    public function confirmPayment(): void
    {
        $user = auth()->user();
        $plan = $this->selectedPlan;

        if (!$plan) {
            return;
        }

        $this->validate([
            'selectedPlanId' => 'required|exists:subscription_plans,id',
        ]);

        $orderId = 'SUB-' . $user->id . '-' . now()->format('YmdHis');

        $pakasir = new PakasirService();

        $response = $pakasir->createTransaction($orderId, $plan->price);

        if (isset($response['payment'])) {
            $payment = $response['payment'];

            $order = SubscriptionOrder::create([
                'user_id' => $user->id,
                'subscription_plan_id' => $this->selectedPlanId,
                'payment_method_id' => null,
                'status' => 'pending',
                'pakasir_order_id' => $orderId,
                'pakasir_payment_number' => $payment['payment_number'] ?? '',
                'total_payment' => $payment['total_payment'] ?? $plan->price,
                'notes' => 'QRIS via Pakasir',
            ]);

            $this->paymentQRIS = $payment['payment_number'] ?? '';
            $this->paymentPaymentNumber = $payment['payment_number'] ?? '';
            $this->pakasirOrderId = $orderId;
            $this->totalPayment = $payment['total_payment'] ?? $plan->price;
            $this->paymentPollingState = 'waiting';
        } else {
            session()->flash('error', 'Failed to create payment. Please try again.');
            $this->paymentPollingState = 'error';
        }
    }

    public function checkPaymentStatus(): void
    {
        $user = auth()->user();
        $plan = $this->selectedPlan;

        if (!$this->pakasirOrderId || !$plan) {
            return;
        }

        $pakasir = new PakasirService();

        $result = $pakasir->getTransactionStatus($this->pakasirOrderId, $plan->price);

        if (($result['transaction']['status'] ?? '') === 'completed') {
            $this->paymentPollingState = 'completed';
            $this->paymentQRIS = '';

            session()->flash('success', 'Payment received! Your subscription is now active.');
        } else {
            $order = SubscriptionOrder::where('pakasir_order_id', $this->pakasirOrderId)
                ->where('user_id', $user->id)
                ->first();

            if ($order && $order->status === 'approved') {
                $this->paymentPollingState = 'completed';
                $this->paymentQRIS = '';
                session()->flash('success', 'Payment received! Your subscription is now active.');
            } else {
                $this->paymentPollingState = 'waiting';
            }
        }
    }

    public function closeModal(): void
    {
        $this->showPaymentModal = false;
        $this->selectedPlan = null;
        $this->selectedPlanId = null;
        $this->paymentQRIS = '';
        $this->paymentPaymentNumber = '';
        $this->pakasirOrderId = null;
        $this->totalPayment = null;
        $this->paymentPollingState = '';
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

    public function render()
    {
        $user = auth()->user();
        $pendingOrder = $this->getPendingOrder();

        return view('livewire.user.subscription', [
            'user' => $user,
            'plans' => $this->plans,
            'isSubscribed' => $user->isSubscribed(),
            'remainingGenerations' => $user->getRemainingGenerations(),
            'dailyLimit' => $user->getDailyLimit(),
            'todayCount' => $user->getTodayGenerationCount(),
            'hasPendingOrder' => $pendingOrder !== null,
            'pendingOrder' => $pendingOrder,
            'recentOrders' => $this->recentOrders,
        ]);
    }

    #[Computed(cache: true, seconds: 60)]
    public function plans()
    {
        return SubscriptionPlan::getActive();
    }

    #[Computed(cache: true, seconds: 30)]
    public function pendingOrder()
    {
        return auth()->user()->latestPendingOrder();
    }

    protected function getPendingOrder(): ?SubscriptionOrder
    {
        return SubscriptionOrder::where('user_id', auth()->id())
            ->where('status', 'pending')
            ->latest()
            ->first();
    }

    #[Computed(cache: true, seconds: 30)]
    public function recentOrders()
    {
        return auth()->user()->subscriptionOrders()->latest()->take(5)->get();
    }
}
