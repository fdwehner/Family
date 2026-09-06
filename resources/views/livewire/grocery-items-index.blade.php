<div>
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-3xl font-bold text-secondary-500 dark:text-white">{{ __('grocery.title') }}</h1>
            <p class="mt-1 text-gray-400 dark:text-gray-400">{{ __('grocery.subtitle') }}</p>
        </div>
        <div class="flex flex-wrap items-center gap-2 shrink-0">
            @can('clearPurchased', App\Models\GroceryItem::class)
                @if ($stats['purchased'] > 0)
                    <button
                        type="button"
                        wire:click="requestClearPurchased"
                        class="rounded-lg border border-gray-200 px-4 py-2 text-sm dark:border-gray-600"
                    >
                        {{ __('grocery.clear_purchased.action') }}
                    </button>
                @endif
            @endcan
            @can('viewAny', App\Models\GroceryProduct::class)
                <a href="{{ route('master-data.grocery-products.index') }}" class="rounded-lg bg-secondary-500 px-4 py-2 text-white dark:bg-white dark:text-secondary-500">
                    {{ __('grocery.master_data.nav') }}
                </a>
            @endcan
        </div>
    </div>

    <div class="mb-3 rounded-lg border border-gray-200 bg-white shadow dark:border-gray-600 dark:bg-secondary-500" x-data="{ statsOpen: false }">
        <button
            type="button"
            data-collapsed-panel-toggle
            class="flex w-full items-center justify-between gap-3 px-4 py-3 text-left"
            @click="statsOpen = !statsOpen"
        >
            <span class="flex items-center gap-2 font-medium">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 3v18M5 8h6M5 16h6M13 7h6M13 12h6M13 17h6" />
                </svg>
                {{ __('grocery.stats.tab_statistics') }}
            </span>
            <span class="flex flex-wrap gap-2 text-xs">
                <span class="rounded-full bg-gray-100 px-2 py-1 dark:bg-secondary-600">{{ __('grocery.stats.total') }}: {{ $stats['total'] }}</span>
                <span class="rounded-full bg-gray-100 px-2 py-1 dark:bg-secondary-600">{{ __('grocery.stats.needed') }}: {{ $stats['needed'] }}</span>
                <span class="rounded-full bg-gray-100 px-2 py-1 dark:bg-secondary-600">{{ __('grocery.stats.purchased') }}: {{ $stats['purchased'] }}</span>
            </span>
        </button>
        <div x-cloak x-show="statsOpen" x-transition class="grid gap-3 border-t border-gray-200 px-4 py-4 sm:grid-cols-3 dark:border-gray-600">
            <div class="rounded-lg border border-gray-200 p-4 dark:border-gray-600">
                <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('grocery.stats.total') }}</p>
                <p class="text-2xl font-semibold">{{ $stats['total'] }}</p>
            </div>
            <div class="rounded-lg border border-gray-200 p-4 dark:border-gray-600">
                <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('grocery.stats.needed') }}</p>
                <p class="text-2xl font-semibold">{{ $stats['needed'] }}</p>
            </div>
            <div class="rounded-lg border border-gray-200 p-4 dark:border-gray-600">
                <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('grocery.stats.purchased') }}</p>
                <p class="text-2xl font-semibold">{{ $stats['purchased'] }}</p>
            </div>
        </div>
    </div>

    <div class="mb-6 rounded-lg border border-gray-200 bg-white shadow dark:border-gray-600 dark:bg-secondary-500" x-data="{ filtersOpen: @js($filtersOpen) }">
        <button type="button" class="flex w-full items-center justify-between gap-3 px-4 py-3 text-left" @click="filtersOpen = !filtersOpen">
            <span class="flex items-center gap-2 font-medium">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4h18M6 12h12M10 20h4" />
                </svg>
                {{ __('common.actions.search') }} / {{ __('common.actions.filter') }}
            </span>
            @if ($filtersActive)
                <span class="rounded-full bg-gray-100 px-2 py-1 text-xs dark:bg-secondary-600">{{ $products->total() }}</span>
            @endif
        </button>
        <div x-cloak x-show="filtersOpen" x-transition class="border-t border-gray-200 px-4 py-4 dark:border-gray-600">
            <div class="grid gap-4 sm:grid-cols-3">
                <div>
                    <label class="mb-2 block text-sm font-medium text-secondary-500 dark:text-gray-200">{{ __('common.actions.search') }}</label>
                    <input
                        type="search"
                        wire:model.live.debounce.500ms="search"
                        placeholder="{{ __('grocery.filters.search_placeholder') }}"
                        class="w-full rounded-lg border border-gray-200 px-4 py-3 text-base touch-manipulation dark:border-gray-400 dark:bg-secondary-600"
                    >
                </div>
                <div>
                    <label class="mb-2 block text-sm font-medium text-secondary-500 dark:text-gray-200">{{ __('grocery.filters.status') }}</label>
                    <select wire:model.live="status" class="w-full rounded-lg border border-gray-200 px-4 py-3 text-base touch-manipulation dark:border-gray-400 dark:bg-secondary-600">
                        <option value="">{{ __('grocery.filters.all_statuses') }}</option>
                        <option value="needed">{{ __('grocery.filters.needed') }}</option>
                        <option value="purchased">{{ __('grocery.filters.purchased') }}</option>
                    </select>
                </div>
                <div>
                    <label class="mb-2 block text-sm font-medium text-secondary-500 dark:text-gray-200">{{ __('grocery.filters.category') }}</label>
                    <select wire:model.live="category" class="w-full rounded-lg border border-gray-200 px-4 py-3 text-base touch-manipulation dark:border-gray-400 dark:bg-secondary-600">
                        <option value="">{{ __('grocery.filters.all_categories') }}</option>
                        @foreach ($categories as $categoryKey)
                            <option value="{{ $categoryKey }}">{{ __('grocery.categories.'.$categoryKey) }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            @if ($filtersActive)
                <button type="button" wire:click="clearFilters" class="mt-4 text-sm underline">
                    {{ __('common.actions.clear_filters') }}
                </button>
            @endif
        </div>
    </div>

    <div wire:loading class="mb-4 flex items-center justify-center gap-2 text-sm text-gray-500">
        <svg class="h-5 w-5 animate-spin" viewBox="0 0 24 24" fill="none" aria-hidden="true">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
        </svg>
        <span>{{ __('common.actions.loading') }}</span>
    </div>

    <div wire:loading.remove>
        @if ($products->isEmpty())
            <div class="rounded-lg border border-gray-200 bg-white px-6 py-12 text-center shadow-md dark:border-gray-600 dark:bg-secondary-500">
                <p class="text-gray-500 dark:text-gray-400">
                    {{ $filtersActive ? __('grocery.empty_filtered') : __('grocery.empty') }}
                </p>
            </div>
        @else
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($products as $product)
                    @php
                        $line = $listItems->get($product->id);
                        $quantity = $line ? $line->formattedQuantity() : '0';
                        $onList = $line !== null;
                    @endphp
                    <article
                        wire:key="grocery-product-{{ $product->id }}"
                        class="flex flex-col rounded-lg border bg-white p-4 shadow-md dark:bg-secondary-500 {{ $line?->is_purchased ? 'border-gray-200 opacity-70 dark:border-gray-600' : ($onList ? 'border-secondary-500 dark:border-white' : 'border-gray-200 dark:border-gray-600') }}"
                    >
                        <div class="mb-3 flex items-start justify-between gap-2">
                            <p class="text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                {{ $product->categoryLabel() }}
                            </p>
                            @if ($onList)
                                @can('togglePurchased', $line)
                                    <button
                                        type="button"
                                        wire:click="togglePurchased({{ $line->id }})"
                                        class="rounded-lg p-1 hover:bg-gray-100 dark:hover:bg-secondary-600"
                                        title="{{ $line->is_purchased ? __('grocery.mark_needed') : __('grocery.mark_purchased') }}"
                                        aria-label="{{ $line->is_purchased ? __('grocery.mark_needed') : __('grocery.mark_purchased') }}"
                                    >
                                        @if ($line->is_purchased)
                                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h4l3 8 4-16 3 8h4" /></svg>
                                        @else
                                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                                        @endif
                                    </button>
                                @endcan
                            @endif
                        </div>

                        <div class="mb-3 flex justify-center">
                            @if ($product->imageUrl())
                                <img
                                    src="{{ $product->imageUrl() }}"
                                    alt="{{ $product->displayName() }}"
                                    class="h-28 w-28 object-contain {{ $line?->is_purchased ? 'grayscale' : '' }}"
                                >
                            @else
                                <div class="flex h-28 w-28 items-center justify-center rounded-2xl bg-gray-100 text-3xl font-semibold text-secondary-500 dark:bg-secondary-600 dark:text-white" aria-hidden="true">
                                    {{ mb_strtoupper(mb_substr($product->displayName(), 0, 1)) }}
                                </div>
                            @endif
                        </div>

                        <h2 class="text-center text-lg font-semibold {{ $line?->is_purchased ? 'line-through text-gray-400' : '' }}">
                            {{ $product->displayName() }}
                        </h2>
                        @if ($product->brand)
                            <p class="text-center text-sm text-gray-500 dark:text-gray-400">{{ $product->brand }}</p>
                        @endif
                        <p class="mb-4 text-center text-sm text-gray-500 dark:text-gray-400">{{ $product->unitLabel() }}</p>

                        <div class="mt-auto flex items-center justify-center gap-3">
                            <button
                                type="button"
                                wire:click="decrementProduct({{ $product->id }})"
                                wire:loading.attr="disabled"
                                @disabled(! $onList)
                                class="flex h-11 w-11 items-center justify-center rounded-full border border-gray-200 text-secondary-500 enabled:hover:bg-gray-50 disabled:cursor-not-allowed disabled:opacity-40 dark:border-gray-600 dark:text-white dark:enabled:hover:bg-secondary-600"
                                title="{{ __('grocery.list.remove') }}"
                                aria-label="{{ __('grocery.list.remove') }}"
                            >
                                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14" />
                                </svg>
                            </button>
                            <div class="min-w-[3rem] text-center">
                                <span class="text-xl font-semibold">{{ $quantity }}</span>
                                <span class="sr-only">{{ __('grocery.table.quantity') }}</span>
                            </div>
                            <button
                                type="button"
                                wire:click="incrementProduct({{ $product->id }})"
                                wire:loading.attr="disabled"
                                class="flex h-11 w-11 items-center justify-center rounded-full bg-secondary-500 text-white hover:opacity-90 dark:bg-white dark:text-secondary-500"
                                title="{{ __('grocery.list.add') }}"
                                aria-label="{{ __('grocery.list.add') }}"
                            >
                                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v14M5 12h14" />
                                </svg>
                            </button>
                        </div>
                    </article>
                @endforeach
            </div>
        @endif

        @if ($products->hasPages())
            <div class="mt-6 border-t border-gray-200 px-2 py-4 dark:border-gray-600">
                {{ $products->links() }}
            </div>
        @endif
    </div>

    <livewire:modals.confirmation-modal />
</div>
