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
            @can('create', App\Models\GroceryItem::class)
                <a href="{{ route('groceries.create') }}" class="rounded-lg bg-secondary-500 px-4 py-2 text-white dark:bg-white dark:text-secondary-500">
                    {{ __('grocery.add_item') }}
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
                <span class="rounded-full bg-gray-100 px-2 py-1 text-xs dark:bg-secondary-600">{{ $items->total() }}</span>
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

    <div wire:loading.remove class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-md dark:border-gray-600 dark:bg-secondary-500">
        <div class="overflow-x-auto">
            <table class="min-w-full w-full divide-y divide-gray-200 dark:divide-gray-600">
                <thead class="bg-gray-50 dark:bg-secondary-600">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">{{ __('grocery.table.item') }}</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">{{ __('grocery.table.quantity') }}</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">{{ __('grocery.table.category') }}</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">{{ __('grocery.table.status') }}</th>
                        <th class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">{{ __('grocery.table.actions') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-600">
                    @forelse ($items as $item)
                        <tr wire:key="grocery-item-{{ $item->id }}" class="hover:bg-gray-50 dark:hover:bg-secondary-600 transition-colors">
                            <td class="px-4 py-3">
                                <div class="font-medium {{ $item->is_purchased ? 'line-through text-gray-400' : '' }}">{{ $item->name }}</div>
                                @if ($item->notes)
                                    <div class="text-sm text-gray-400">{{ $item->notes }}</div>
                                @endif
                            </td>
                            <td class="whitespace-nowrap px-4 py-3">
                                {{ $item->quantity }} {{ $item->unitLabel() }}
                            </td>
                            <td class="whitespace-nowrap px-4 py-3">{{ $item->categoryLabel() }}</td>
                            <td class="whitespace-nowrap px-4 py-3">
                                {{ $item->is_purchased ? __('grocery.table.purchased') : __('grocery.table.needed') }}
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 text-right">
                                <div class="flex justify-end gap-1">
                                    @can('togglePurchased', $item)
                                        <button
                                            type="button"
                                            wire:click="togglePurchased({{ $item->id }})"
                                            class="rounded-lg p-2 hover:bg-gray-100 dark:hover:bg-secondary-500"
                                            title="{{ $item->is_purchased ? __('grocery.mark_needed') : __('grocery.mark_purchased') }}"
                                            aria-label="{{ $item->is_purchased ? __('grocery.mark_needed') : __('grocery.mark_purchased') }}"
                                        >
                                            @if ($item->is_purchased)
                                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h4l3 8 4-16 3 8h4" /></svg>
                                            @else
                                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                                            @endif
                                        </button>
                                    @endcan
                                    @can('update', $item)
                                        <a
                                            href="{{ route('groceries.edit', $item) }}"
                                            class="rounded-lg p-2 hover:bg-gray-100 dark:hover:bg-secondary-500"
                                            title="{{ __('common.actions.edit') }}"
                                            aria-label="{{ __('common.actions.edit') }}"
                                        >
                                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z" /></svg>
                                        </a>
                                    @endcan
                                    @can('delete', $item)
                                        <button
                                            type="button"
                                            wire:click="requestDelete({{ $item->id }})"
                                            class="rounded-lg p-2 hover:bg-red-50 dark:hover:bg-red-900/30"
                                            title="{{ __('common.actions.delete') }}"
                                            aria-label="{{ __('common.actions.delete') }}"
                                        >
                                            <svg class="h-5 w-5 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 7h12M9 7V5a1 1 0 011-1h4a1 1 0 011 1v2m2 0v12a2 2 0 01-2 2H8a2 2 0 01-2-2V7h12z" /></svg>
                                        </button>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">
                                {{ $filtersActive ? __('grocery.empty_filtered') : __('grocery.empty') }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($items->hasPages())
            <div class="border-t border-gray-200 px-6 py-4 dark:border-gray-600">
                {{ $items->links() }}
            </div>
        @endif
    </div>

    <livewire:modals.confirmation-modal />
</div>
