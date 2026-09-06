<div>
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-3xl font-bold text-secondary-500 dark:text-white">
                {{ $isEdit ? __('grocery.master_data.edit_title') : __('grocery.master_data.create_title') }}
            </h1>
        </div>
        <div class="flex flex-wrap items-center gap-2 shrink-0">
            <x-back-button :href="route('master-data.grocery-products.index')">{{ __('common.actions.back') }}</x-back-button>
        </div>
    </div>

    <form wire:submit="save" class="rounded-lg border border-gray-200 bg-white p-6 shadow-md dark:border-gray-600 dark:bg-secondary-500">
        <div class="grid gap-4 sm:grid-cols-2">
            <div class="sm:col-span-2">
                <label class="mb-2 block text-sm font-medium text-secondary-500 dark:text-gray-200">{{ __('grocery.master_data.name') }}</label>
                <input wire:model.blur="name" type="text" class="w-full rounded-lg border border-gray-200 px-4 py-3 text-base touch-manipulation dark:border-gray-400 dark:bg-secondary-600" placeholder="{{ __('grocery.master_data.name_placeholder') }}">
                @error('name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="mb-2 block text-sm font-medium text-secondary-500 dark:text-gray-200">{{ __('grocery.master_data.brand') }}</label>
                <input wire:model.blur="brand" type="text" class="w-full rounded-lg border border-gray-200 px-4 py-3 text-base touch-manipulation dark:border-gray-400 dark:bg-secondary-600" placeholder="{{ __('grocery.master_data.brand_placeholder') }}">
                @error('brand') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="mb-2 block text-sm font-medium text-secondary-500 dark:text-gray-200">{{ __('grocery.create.unit') }}</label>
                <select wire:model.live="unit" class="w-full rounded-lg border border-gray-200 px-4 py-3 text-base touch-manipulation dark:border-gray-400 dark:bg-secondary-600">
                    @foreach ($units as $unitKey)
                        <option value="{{ $unitKey }}">{{ __('grocery.units.'.$unitKey) }}</option>
                    @endforeach
                </select>
                @error('unit') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="mb-2 block text-sm font-medium text-secondary-500 dark:text-gray-200">{{ __('grocery.create.category') }}</label>
                <select wire:model.live="category" class="w-full rounded-lg border border-gray-200 px-4 py-3 text-base touch-manipulation dark:border-gray-400 dark:bg-secondary-600">
                    @foreach ($categories as $categoryKey)
                        <option value="{{ $categoryKey }}">{{ __('grocery.categories.'.$categoryKey) }}</option>
                    @endforeach
                </select>
                @error('category') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="mb-2 block text-sm font-medium text-secondary-500 dark:text-gray-200">{{ __('grocery.master_data.sort_order') }}</label>
                <input wire:model.blur="sortOrder" type="number" min="0" max="9999" class="w-full rounded-lg border border-gray-200 px-4 py-3 text-base touch-manipulation dark:border-gray-400 dark:bg-secondary-600">
                @error('sortOrder') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>
            <div class="sm:col-span-2">
                <label class="mb-2 block text-sm font-medium text-secondary-500 dark:text-gray-200">{{ __('grocery.master_data.picture') }}</label>
                <input wire:model="photo" type="file" accept="image/jpeg,image/png,image/webp" class="w-full rounded-lg border border-gray-200 px-4 py-3 text-base touch-manipulation dark:border-gray-400 dark:bg-secondary-600">
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('grocery.master_data.picture_help') }}</p>
                @error('photo') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                <div class="mt-3">
                    @if ($photo)
                        <img src="{{ $photo->temporaryUrl() }}" alt="{{ __('grocery.master_data.picture_preview') }}" class="h-28 w-28 object-contain">
                    @elseif ($groceryProduct?->imageUrl())
                        <img src="{{ $groceryProduct->imageUrl() }}" alt="{{ $groceryProduct->displayName() }}" class="h-28 w-28 object-contain">
                    @endif
                </div>
            </div>
            <div class="sm:col-span-2">
                <label class="inline-flex items-center gap-2 text-sm font-medium text-secondary-500 dark:text-gray-200">
                    <input wire:model="isFeatured" type="checkbox" class="h-4 w-4 rounded border-gray-300">
                    {{ __('grocery.master_data.show_on_list') }}
                </label>
            </div>
        </div>

        <div class="mt-6 flex flex-wrap gap-2">
            <button type="submit" wire:loading.attr="disabled" class="rounded-lg bg-secondary-500 px-4 py-2 text-white dark:bg-white dark:text-secondary-500">
                <span wire:loading.remove wire:target="save">
                    {{ $isEdit ? __('grocery.master_data.update_button') : __('grocery.master_data.create_button') }}
                </span>
                <span wire:loading wire:target="save">{{ __('common.actions.loading') }}</span>
            </button>
            <a href="{{ route('master-data.grocery-products.index') }}" class="rounded-lg border border-gray-200 px-4 py-2 dark:border-gray-600">
                {{ __('common.actions.cancel') }}
            </a>
        </div>
    </form>
</div>
