<?php

namespace App\Livewire\Admin;

use App\Models\License;
use App\Models\LicenseOrder;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
#[Title('License Orders')]
class LicenseOrders extends Component
{
    use WithPagination;

    public string $search = '';
    public string $statusFilter = '';
    public bool $showDetailModal = false;
    public ?LicenseOrder $selectedOrder = null;
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
        $this->selectedOrder = LicenseOrder::with(['user', 'licensePlan', 'paymentMethod', 'license'])->findOrFail($id);
        $this->adminNotes = $this->selectedOrder->admin_notes ?? '';
        $this->showDetailModal = true;
    }

    public function approve(): void
    {
        if (!$this->selectedOrder || !$this->selectedOrder->isPending()) {
            return;
        }

        $plan = $this->selectedOrder->licensePlan;

        // Create license based on plan type
        $licenseData = [
            'user_id' => $this->selectedOrder->user_id,
            'product_name' => $plan->product_name,
            'status' => 'active',
            'license_type' => $plan->license_type ?? 'duration',
            'activated_at' => now(),
            'max_activations' => $plan->max_activations,
        ];

        if ($plan->license_type === 'credits') {
            $licenseData['credits_total'] = $plan->credits_amount;
            $licenseData['credits_used'] = 0;
            $licenseData['expires_at'] = null;
        } else {
            $durationDays = $plan->duration_days ? (int) $plan->duration_days : null;
            $licenseData['expires_at'] = $durationDays ? now()->addDays($durationDays) : null;
            $licenseData['credits_total'] = null;
            $licenseData['credits_used'] = 0;
        }

        $license = License::create($licenseData);

        // Update order
        $this->selectedOrder->update([
            'status' => 'approved',
            'license_id' => $license->id,
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
        $query = LicenseOrder::with(['user', 'licensePlan', 'paymentMethod'])
            ->when($this->search, function ($q) {
                $q->whereHas('user', fn($q) => $q->where('name', 'like', "%{$this->search}%")
                    ->orWhere('email', 'like', "%{$this->search}%"));
            })
            ->when($this->statusFilter, fn($q) => $q->where('status', $this->statusFilter))
            ->latest();

        return view('livewire.admin.license-orders', [
            'orders' => $query->paginate(15),
            'stats' => [
                'pending' => LicenseOrder::pending()->count(),
                'approved' => LicenseOrder::approved()->count(),
                'total' => LicenseOrder::count(),
            ],
        ]);
    }
}
