<?php

namespace App\Livewire\Admin;

use App\Models\Product;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use Illuminate\Support\Str;

#[Layout('components.layouts.app')]
#[Title('Products')]
class Products extends Component
{
    use WithPagination, WithFileUploads;

    public string $search = '';
    public string $typeFilter = '';
    public bool $showModal = false;
    public bool $isEditing = false;
    public ?int $editingId = null;

    // Form fields
    public string $name = '';
    public string $slug = '';
    public string $short_description = '';
    public string $description = '';
    public $thumbnail = null;
    public ?string $existingThumbnail = null;
    public string $type = 'paid';
    public int $price = 0;
    public ?int $sale_price = null;
    public $productFile = null;
    public ?string $existingFile = null;
    public ?string $existingFileName = null;
    public string $version = '1.0.0';
    public string $demo_url = '';
    public string $documentation_url = '';
    public string $features = '';
    public string $requirements = '';
    public bool $requires_license = false;
    public ?int $license_duration_days = null;
    public bool $is_active = true;
    public bool $is_featured = false;
    public int $sort_order = 0;

    protected function rules(): array
    {
        $rules = [
            'name' => 'required|min:2|max:255',
            'slug' => 'nullable|max:255|unique:products,slug,' . $this->editingId,
            'short_description' => 'nullable|max:500',
            'description' => 'nullable',
            'thumbnail' => 'nullable|image|max:2048',
            'type' => 'required|in:free,paid',
            'price' => 'integer|min:0',
            'sale_price' => 'nullable|integer|min:0',
            'productFile' => 'nullable|file|max:102400',
            'version' => 'required|max:20',
            'demo_url' => 'nullable|max:255',
            'documentation_url' => 'nullable|max:255',
            'requires_license' => 'boolean',
            'license_duration_days' => 'nullable|integer|min:1',
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
            'sort_order' => 'integer|min:0',
        ];

        // Only validate sale_price < price if both are set and type is paid
        if ($this->type === 'paid' && $this->sale_price !== null && $this->price > 0) {
            $rules['sale_price'] = 'nullable|integer|min:0|lt:price';
        }

        return $rules;
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatedName(): void
    {
        if (!$this->isEditing) {
            $this->slug = Str::slug($this->name);
        }
    }

    public function openCreateModal(): void
    {
        $this->resetForm();
        $this->showModal = true;
        $this->isEditing = false;
    }

    public function openEditModal(int $id): void
    {
        $product = Product::findOrFail($id);

        $this->editingId = $product->id;
        $this->name = $product->name;
        $this->slug = $product->slug;
        $this->short_description = $product->short_description ?? '';
        $this->description = $product->description ?? '';
        $this->existingThumbnail = $product->thumbnail;
        $this->type = $product->type;
        $this->price = $product->price;
        $this->sale_price = $product->sale_price;
        $this->existingFile = $product->file_path;
        $this->existingFileName = $product->file_name;
        $this->version = $product->version;
        $this->demo_url = $product->demo_url ?? '';
        $this->documentation_url = $product->documentation_url ?? '';
        $this->features = $product->features ? implode("\n", $product->features) : '';
        $this->requirements = $product->requirements ? implode("\n", $product->requirements) : '';
        $this->requires_license = $product->requires_license;
        $this->license_duration_days = $product->license_duration_days;
        $this->is_active = $product->is_active;
        $this->is_featured = $product->is_featured;
        $this->sort_order = $product->sort_order;

        $this->showModal = true;
        $this->isEditing = true;
    }

    public function save(): void
    {
        $this->validate();

        try {
            $features = array_filter(array_map('trim', explode("\n", $this->features)));
            $requirements = array_filter(array_map('trim', explode("\n", $this->requirements)));

            $data = [
                'name' => $this->name,
                'slug' => $this->slug ?: Str::slug($this->name),
                'short_description' => $this->short_description ?: null,
                'description' => $this->description ?: null,
                'type' => $this->type,
                'price' => $this->type === 'free' ? 0 : $this->price,
                'sale_price' => $this->type === 'free' ? null : $this->sale_price,
                'version' => $this->version,
                'demo_url' => $this->demo_url ?: null,
                'documentation_url' => $this->documentation_url ?: null,
                'features' => !empty($features) ? $features : null,
                'requirements' => !empty($requirements) ? $requirements : null,
                'requires_license' => $this->requires_license,
                'license_duration_days' => $this->requires_license ? $this->license_duration_days : null,
                'is_active' => $this->is_active,
                'is_featured' => $this->is_featured,
                'sort_order' => $this->sort_order,
            ];

            // Handle thumbnail upload - store in storage/app/public
            if ($this->thumbnail) {
                $filename = time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '', $this->thumbnail->getClientOriginalName());
                $this->thumbnail->storeAs('products/thumbnails', $filename, 'public');
                $data['thumbnail'] = 'storage/products/thumbnails/' . $filename;
            }

            // Handle product file upload - store in storage/app/private (secure)
            if ($this->productFile) {
                $filename = time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '', $this->productFile->getClientOriginalName());
                $this->productFile->storeAs('products/files', $filename, 'local');
                $data['file_path'] = 'products/files/' . $filename;
                $data['file_name'] = $this->productFile->getClientOriginalName();
                $data['file_size'] = $this->productFile->getSize();
            }

            if ($this->isEditing) {
                Product::find($this->editingId)->update($data);
                session()->flash('success', 'Product updated successfully!');
            } else {
                Product::create($data);
                session()->flash('success', 'Product created successfully!');
            }

            $this->closeModal();
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to save product: ' . $e->getMessage());
        }
    }

    public function toggleActive(int $id): void
    {
        $product = Product::findOrFail($id);
        $product->update(['is_active' => !$product->is_active]);
    }

    public function toggleFeatured(int $id): void
    {
        $product = Product::findOrFail($id);
        $product->update(['is_featured' => !$product->is_featured]);
    }

    public function delete(int $id): void
    {
        Product::findOrFail($id)->delete();
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->resetForm();
    }

    protected function resetForm(): void
    {
        $this->name = '';
        $this->slug = '';
        $this->short_description = '';
        $this->description = '';
        $this->thumbnail = null;
        $this->existingThumbnail = null;
        $this->type = 'paid';
        $this->price = 0;
        $this->sale_price = null;
        $this->productFile = null;
        $this->existingFile = null;
        $this->existingFileName = null;
        $this->version = '1.0.0';
        $this->demo_url = '';
        $this->documentation_url = '';
        $this->features = '';
        $this->requirements = '';
        $this->requires_license = false;
        $this->license_duration_days = null;
        $this->is_active = true;
        $this->is_featured = false;
        $this->sort_order = 0;
        $this->editingId = null;
        $this->resetValidation();
    }

    public function render()
    {
        $query = Product::query()
            ->when($this->search, fn($q) => $q->where('name', 'like', "%{$this->search}%"))
            ->when($this->typeFilter, fn($q) => $q->where('type', $this->typeFilter))
            ->ordered();

        return view('livewire.admin.products', [
            'products' => $query->paginate(12),
            'stats' => [
                'total' => Product::count(),
                'active' => Product::where('is_active', true)->count(),
                'free' => Product::where('type', 'free')->count(),
                'paid' => Product::where('type', 'paid')->count(),
            ],
        ]);
    }
}
