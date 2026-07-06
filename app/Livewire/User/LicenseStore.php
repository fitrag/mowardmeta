<?php

namespace App\Livewire\User;

use App\Models\License;
use App\Models\LicenseOrder;
use App\Models\LicensePlan;
use App\Models\PaymentMethod;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('components.layouts.app')]
#[Title('License Store')]
class LicenseStore extends Component
{
    use WithFileUploads;

    public bool $showOrderModal = false;
    public ?LicensePlan $selectedPlan = null;
    public string $selectedPaymentMethodId = '';
    public $proofOfPayment = null;
    public string $notes = '';

    protected function rules(): array
    {
        // Free plans don't need payment method
        if ($this->selectedPlan && $this->selectedPlan->price <= 0) {
            return [
                'proofOfPayment' => 'nullable|image|max:2048',
                'notes' => 'nullable|string|max:500',
            ];
        }

        return [
            'selectedPaymentMethodId' => 'required|exists:payment_methods,id',
            'proofOfPayment' => 'nullable|image|max:2048',
            'notes' => 'nullable|string|max:500',
        ];
    }

    public function selectPlan(int $planId): void
    {
        $this->selectedPlan = LicensePlan::findOrFail($planId);
        
        // If free, process immediately without modal
        if ($this->selectedPlan->price <= 0) {
            $this->processFreeLicense();
            return;
        }
        
        $this->showOrderModal = true;
    }

    protected function processFreeLicense(): void
    {
        $plan = $this->selectedPlan;

        // Create license directly
        $licenseData = [
            'user_id' => auth()->id(),
            'product_name' => $plan->product_name,
            'status' => 'active',
            'license_type' => $plan->license_type ?? 'duration',
            'activated_at' => now(),
            'max_activations' => $plan->max_activations,
        ];

        if ($plan->license_type === 'credits') {
            $licenseData['credits_total'] = $plan->credits_amount;
            $licenseData['credits_used'] = 0;
        } else {
            $licenseData['expires_at'] = $plan->duration_days ? now()->addDays($plan->duration_days) : null;
        }

        $license = License::create($licenseData);

        // Create order record (auto-approved)
        LicenseOrder::create([
            'user_id' => auth()->id(),
            'license_plan_id' => $plan->id,
            'license_id' => $license->id,
            'status' => 'approved',
            'processed_at' => now(),
            'notes' => 'Free license - auto approved',
        ]);

        $this->selectedPlan = null;
        session()->flash('success', 'License activated successfully!');
    }

    public function submitOrder(): void
    {
        $this->validate();

        $proofPath = null;
        if ($this->proofOfPayment) {
            $proofPath = $this->proofOfPayment->store('license-proofs', 'public');
        }

        LicenseOrder::create([
            'user_id' => auth()->id(),
            'license_plan_id' => $this->selectedPlan->id,
            'payment_method_id' => $this->selectedPaymentMethodId,
            'proof_of_payment' => $proofPath,
            'notes' => $this->notes ?: null,
            'status' => 'pending',
        ]);

        $this->closeModal();
        session()->flash('success', 'Order submitted successfully! Please wait for admin approval.');
    }

    public function closeModal(): void
    {
        $this->showOrderModal = false;
        $this->selectedPlan = null;
        $this->selectedPaymentMethodId = '';
        $this->proofOfPayment = null;
        $this->notes = '';
        $this->resetValidation();
    }

    public function cancelOrder(int $orderId): void
    {
        $order = LicenseOrder::where('user_id', auth()->id())
            ->where('id', $orderId)
            ->pending()
            ->firstOrFail();

        $order->delete();
        session()->flash('success', 'Order cancelled successfully.');
    }

    public function render()
    {
        $user = auth()->user();

        return view('livewire.user.license-store', [
            'plans' => LicensePlan::active()->ordered()->get(),
            'paymentMethods' => PaymentMethod::where('is_active', true)->get(),
            'myLicenses' => License::where('user_id', $user->id)->latest()->get(),
            'pendingOrders' => LicenseOrder::where('user_id', $user->id)->pending()->with('licensePlan')->get(),
            'recentOrders' => LicenseOrder::where('user_id', $user->id)->with(['licensePlan', 'license'])->latest()->take(10)->get(),
        ]);
    }
}
