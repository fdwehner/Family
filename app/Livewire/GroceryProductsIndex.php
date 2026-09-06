<?php

namespace App\Livewire;

use App\Models\GroceryProduct;
use App\Services\GroceryProductCatalogService;
use App\Support\GroceryCatalog;
use App\Traits\LogsActivity;
use App\Traits\WithToastNotifications;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

class GroceryProductsIndex extends Component
{
    use AuthorizesRequests;
    use LogsActivity;
    use WithPagination;
    use WithToastNotifications;

    public string $search = '';

    public string $category = '';

    public string $featured = '';

    public bool $filtersOpen = false;

    /**
     * @var array<string, array<string, mixed>>
     */
    protected $queryString = [
        'search' => ['except' => ''],
        'category' => ['except' => ''],
        'featured' => ['except' => ''],
    ];

    public function mount(): void
    {
        $this->authorize('viewAny', GroceryProduct::class);
        app(GroceryProductCatalogService::class)->ensureDefaults(auth()->user());
        $this->filtersOpen = request()->anyFilled(['search', 'category', 'featured']);
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingCategory(): void
    {
        $this->resetPage();
    }

    public function updatingFeatured(): void
    {
        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->search = '';
        $this->category = '';
        $this->featured = '';
        $this->resetPage();
    }

    public function requestDelete(int $id): void
    {
        $product = $this->ownedProduct($id);
        $this->authorize('delete', $product);

        $this->dispatch('open-confirmation',
            title: __('grocery.master_data.delete.title'),
            message: __('grocery.master_data.messages.confirm_delete', ['name' => $product->displayName()]),
            confirmEvent: 'delete-grocery-product',
            payload: $id,
        );
    }

    #[On('delete-grocery-product')]
    public function deleteProduct(int $id): void
    {
        try {
            $product = $this->ownedProduct($id);
            $this->authorize('delete', $product);
            $name = $product->displayName();
            $product->delete();
            $this->logCrud('deleted', $product, ['name' => $name]);
            $this->toastSuccess(__('grocery.master_data.messages.deleted'));
        } catch (\Throwable $exception) {
            $this->logError('Failed to delete grocery product', [
                'error' => $exception->getMessage(),
                'grocery_product_id' => $id,
            ]);
            $this->toastError(__('common.messages.error'));
        }
    }

    public function render()
    {
        $this->authorize('viewAny', GroceryProduct::class);

        $user = auth()->user();
        $search = $this->sanitizedSearch();

        $baseQuery = GroceryProduct::query()->forUser($user);

        $stats = [
            'total' => (clone $baseQuery)->count(),
            'featured' => (clone $baseQuery)->where('is_featured', true)->count(),
            'hidden' => (clone $baseQuery)->where('is_featured', false)->count(),
        ];

        $products = GroceryProduct::query()
            ->forUser($user)
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($inner) use ($search) {
                    $inner->where('name', 'like', '%'.$search.'%')
                        ->orWhere('brand', 'like', '%'.$search.'%');
                });
            })
            ->when($this->category !== '', function ($query) {
                $validatedCategory = in_array($this->category, GroceryCatalog::categories(), true)
                    ? $this->category
                    : null;

                if ($validatedCategory) {
                    $query->where('category', $validatedCategory);
                }
            })
            ->when($this->featured === 'yes', fn ($query) => $query->where('is_featured', true))
            ->when($this->featured === 'no', fn ($query) => $query->where('is_featured', false))
            ->orderBy('sort_order')
            ->orderBy('name')
            ->paginate(15);

        $filtersActive = $this->search !== '' || $this->category !== '' || $this->featured !== '';

        return view('livewire.grocery-products-index', [
            'products' => $products,
            'stats' => $stats,
            'categories' => GroceryCatalog::categories(),
            'filtersActive' => $filtersActive,
        ]);
    }

    private function ownedProduct(int $id): GroceryProduct
    {
        return GroceryProduct::query()
            ->forUser(auth()->user())
            ->findOrFail($id);
    }

    private function sanitizedSearch(): string
    {
        $search = trim($this->search);

        if (mb_strlen($search) > 255) {
            $search = mb_substr($search, 0, 255);
        }

        return preg_replace('/[%_]/', '', $search) ?? '';
    }
}
