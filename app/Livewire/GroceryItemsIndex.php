<?php

namespace App\Livewire;

use App\Models\GroceryItem;
use App\Support\GroceryCatalog;
use App\Traits\LogsActivity;
use App\Traits\WithToastNotifications;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
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

    public function requestDelete(int $id): void
    {
        $item = $this->ownedItem($id);
        $this->authorize('delete', $item);

        $this->dispatch('open-confirmation',
            title: __('grocery.delete.title'),
            message: __('grocery.messages.confirm_delete', ['name' => $item->name]),
            confirmEvent: 'delete-grocery-item',
            payload: $id,
        );
    }

    #[On('delete-grocery-item')]
    public function deleteItem(int $id): void
    {
        try {
            $item = $this->ownedItem($id);
            $this->authorize('delete', $item);
            $name = $item->name;
            $item->delete();
            $this->logCrud('deleted', $item, ['name' => $name]);
            $this->toastSuccess(__('grocery.messages.deleted'));
        } catch (\Throwable $exception) {
            $this->logError('Failed to delete grocery item', [
                'error' => $exception->getMessage(),
                'grocery_item_id' => $id,
            ]);
            $this->toastError(__('common.messages.error'));
        }
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

        $items = GroceryItem::query()
            ->forUser($user)
            ->when($search !== '', function ($query) use ($search) {
                $query->where('name', 'like', '%'.$search.'%');
            })
            ->when($this->status === 'needed', fn ($query) => $query->where('is_purchased', false))
            ->when($this->status === 'purchased', fn ($query) => $query->where('is_purchased', true))
            ->when($this->category !== '', function ($query) {
                $validatedCategory = in_array($this->category, GroceryCatalog::categories(), true)
                    ? $this->category
                    : null;

                if ($validatedCategory) {
                    $query->where('category', $validatedCategory);
                }
            })
            ->orderBy('is_purchased')
            ->orderBy('name')
            ->paginate(15);

        $filtersActive = $this->search !== '' || $this->status !== '' || $this->category !== '';

        return view('livewire.grocery-items-index', [
            'items' => $items,
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

    private function sanitizedSearch(): string
    {
        $search = trim($this->search);

        if (mb_strlen($search) > 255) {
            $search = mb_substr($search, 0, 255);
        }

        return preg_replace('/[%_]/', '', $search) ?? '';
    }
}
