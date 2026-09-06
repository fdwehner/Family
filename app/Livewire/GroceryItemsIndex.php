<?php

namespace App\Livewire;

use App\Models\GroceryItem;
use App\Models\GroceryProduct;
use App\Services\GroceryProductCatalogService;
use App\Support\DefaultGroceryCatalog;
use App\Support\GroceryCatalog;
use App\Traits\LogsActivity;
use App\Traits\WithToastNotifications;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

class GroceryItemsIndex extends Component
{
    use AuthorizesRequests;
    use LogsActivity;
    use WithPagination;
    use WithToastNotifications;

    public string $search = '';

    public string $status = '';

    public string $category = '';

    public bool $filtersOpen = false;

    /**
     * @var array<string, array<string, mixed>>
     */
    protected $queryString = [
        'search' => ['except' => ''],
        'status' => ['except' => ''],
        'category' => ['except' => ''],
    ];

    public function mount(): void
    {
        $this->authorize('viewAny', GroceryItem::class);
        app(GroceryProductCatalogService::class)->ensureDefaults(auth()->user());
        $this->filtersOpen = request()->anyFilled(['search', 'status', 'category']);
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingStatus(): void
    {
        $this->resetPage();
    }

    public function updatingCategory(): void
    {
        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->search = '';
        $this->status = '';
        $this->category = '';
        $this->resetPage();
    }

    public function incrementProduct(int $id): void
    {
        $product = $this->ownedProduct($id);
        $this->authorize('view', $product);

        DB::transaction(function () use ($product): void {
            $item = GroceryItem::query()
                ->forUser(auth()->user())
                ->where('grocery_product_id', $product->id)
                ->lockForUpdate()
                ->first();

            if ($item) {
                $this->authorize('update', $item);
                $quantity = min(9999, (float) $item->quantity + 1);
                $item->forceFill(['quantity' => $quantity])->save();

                if ($item->is_purchased) {
                    $item->markUnpurchased();
                }

                return;
            }

            $this->authorize('create', GroceryItem::class);

            GroceryItem::query()->create([
                'user_id' => auth()->id(),
                'grocery_product_id' => $product->id,
                'quantity' => 1,
                'is_purchased' => false,
            ]);
        });
    }

    public function decrementProduct(int $id): void
    {
        $product = $this->ownedProduct($id);
        $this->authorize('view', $product);

        DB::transaction(function () use ($product): void {
            $item = GroceryItem::query()
                ->forUser(auth()->user())
                ->where('grocery_product_id', $product->id)
                ->lockForUpdate()
                ->first();

            if ($item === null) {
                return;
            }

            $quantity = (float) $item->quantity - 1;

            if ($quantity <= 0) {
                $this->authorize('delete', $item);
                $item->delete();

                return;
            }

            $this->authorize('update', $item);
            $item->forceFill(['quantity' => $quantity])->save();
        });
    }

    public function togglePurchased(int $id): void
    {
        $item = $this->ownedItem($id);
        $this->authorize('togglePurchased', $item);

        if ($item->is_purchased) {
            $item->markUnpurchased();
            $this->toastSuccess(__('grocery.messages.marked_needed'));
        } else {
            $item->markPurchased();
            $this->toastSuccess(__('grocery.messages.marked_purchased'));
        }

        $this->logCrud('updated', $item, [
            'is_purchased' => $item->is_purchased,
        ]);
    }

    public function requestClearPurchased(): void
    {
        $this->authorize('clearPurchased', GroceryItem::class);

        $this->dispatch('open-confirmation',
            title: __('grocery.clear_purchased.title'),
            message: __('grocery.clear_purchased.confirm'),
            confirmEvent: 'clear-purchased-grocery-items',
            payload: null,
        );
    }

    #[On('clear-purchased-grocery-items')]
    public function clearPurchased(): void
    {
        $this->authorize('clearPurchased', GroceryItem::class);

        $deleted = GroceryItem::query()
            ->forUser(auth()->user())
            ->where('is_purchased', true)
            ->delete();

        $this->toastSuccess(__('grocery.messages.purchased_cleared', ['count' => $deleted]));
        $this->resetPage();
    }

    public function render()
    {
        $this->authorize('viewAny', GroceryItem::class);

        $user = auth()->user();
        $search = $this->sanitizedSearch();

        $baseQuery = GroceryItem::query()->forUser($user);

        $stats = [
            'total' => (clone $baseQuery)->count(),
            'needed' => (clone $baseQuery)->where('is_purchased', false)->count(),
            'purchased' => (clone $baseQuery)->where('is_purchased', true)->count(),
        ];

        $listItems = GroceryItem::query()
            ->forUser($user)
            ->get()
            ->keyBy('grocery_product_id');

        $onListIds = $listItems->keys()->all();
        $neededIds = $listItems->where('is_purchased', false)->keys()->all();
        $purchasedIds = $listItems->where('is_purchased', true)->keys()->all();

        $products = GroceryProduct::query()
            ->forUser($user)
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($inner) use ($search) {
                    $inner->where('name', 'like', '%'.$search.'%')
                        ->orWhere('brand', 'like', '%'.$search.'%');

                    $matchingSlugs = DefaultGroceryCatalog::slugsMatching($search);
                    if ($matchingSlugs !== []) {
                        $inner->orWhereIn('slug', $matchingSlugs);
                    }
                });
            })
            ->when($this->status === 'needed', fn ($query) => $query->whereIn('id', $neededIds ?: [0]))
            ->when($this->status === 'purchased', fn ($query) => $query->whereIn('id', $purchasedIds ?: [0]))
            ->when($this->status === '', function ($query) use ($onListIds) {
                $query->where(function ($inner) use ($onListIds) {
                    $inner->where('is_featured', true);

                    if ($onListIds !== []) {
                        $inner->orWhereIn('id', $onListIds);
                    }
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
            ->orderBy('sort_order')
            ->orderBy('name')
            ->paginate(24);

        $filtersActive = $this->search !== '' || $this->status !== '' || $this->category !== '';

        return view('livewire.grocery-items-index', [
            'products' => $products,
            'listItems' => $listItems,
            'stats' => $stats,
            'categories' => GroceryCatalog::categories(),
            'filtersActive' => $filtersActive,
        ]);
    }

    private function ownedItem(int $id): GroceryItem
    {
        return GroceryItem::query()
            ->forUser(auth()->user())
            ->findOrFail($id);
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
