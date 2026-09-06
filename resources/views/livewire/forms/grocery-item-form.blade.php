<div>
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-3xl font-bold text-secondary-500 dark:text-white">
                {{ $isEdit ? __('grocery.edit.title') : __('grocery.create.title') }}
            </h1>
        </div>
        <div class="flex flex-wrap items-center gap-2 shrink-0">
            <x-back-button :href="route('groceries.index')">{{ __('common.actions.back') }}</x-back-button>
        </div>
    </div>

    <form wire:submit="save" class="rounded-lg border border-gray-200 bg-white p-6 shadow-md dark:border-gray-600 dark:bg-secondary-500">
        <div class="grid gap-4 sm:grid-cols-2">
            <div class="sm:col-span-2">
                <label class="mb-2 block text-sm font-medium text-secondary-500 dark:text-gray-200">{{ __('grocery.create.name') }}</label>
                <input wire:model.blur="name" type="text" class="w-full rounded-lg border border-gray-200 px-4 py-3 text-base touch-manipulation dark:border-gray-400 dark:bg-secondary-600" placeholder="{{ __('grocery.create.name_placeholder') }}">
                @error('name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="mb-2 block text-sm font-medium text-secondary-500 dark:text-gray-200">{{ __('grocery.create.quantity') }}</label>
                <input wire:model.blur="quantity" type="number" step="0.01" min="0" class="w-full rounded-lg border border-gray-200 px-4 py-3 text-base touch-manipulation dark:border-gray-400 dark:bg-secondary-600">
                @error('quantity') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="mb-2 block text-sm font-medium text-secondary-500 dark:text-gray-200">{{ __('grocery.create.unit') }}</label>
                <select wire:model.live="unit" class="w-full rounded-lg border border-gray-200 px-4 py-3 text-base touch-manipulation dark:border-gray-400 dark:bg-secondary-600">
                    <option value="">{{ __('grocery.create.unit_none') }}</option>
                    @foreach ($units as $unitKey)
                        <option value="{{ $unitKey }}">{{ __('grocery.units.'.$unitKey) }}</option>
                    @endforeach
                </select>
                @error('unit') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>
            <div class="sm:col-span-2">
                <label class="mb-2 block text-sm font-medium text-secondary-500 dark:text-gray-200">{{ __('grocery.create.category') }}</label>
                <select wire:model.live="category" class="w-full rounded-lg border border-gray-200 px-4 py-3 text-base touch-manipulation dark:border-gray-400 dark:bg-secondary-600">
                    @foreach ($categories as $categoryKey)
                        <option value="{{ $categoryKey }}">{{ __('grocery.categories.'.$categoryKey) }}</option>
                    @endforeach
                </select>
                @error('category') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>
            <div class="sm:col-span-2">
                <label class="mb-2 block text-sm font-medium text-secondary-500 dark:text-gray-200">{{ __('grocery.create.notes') }}</label>
                <textarea wire:model.blur="notes" rows="3" class="w-full rounded-lg border border-gray-200 px-4 py-3 text-base touch-manipulation dark:border-gray-400 dark:bg-secondary-600" placeholder="{{ __('grocery.create.notes_placeholder') }}"></textarea>
                @error('notes') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>
        </div>

        <div class="mt-6 flex flex-wrap gap-2">
            <button type="submit" wire:loading.attr="disabled" class="rounded-lg bg-secondary-500 px-4 py-2 text-white dark:bg-white dark:text-secondary-500">
                <span wire:loading.remove wire:target="save">
                    {{ $isEdit ? __('grocery.edit.update_button') : __('grocery.create.create_button') }}
                </span>
                <span wire:loading wire:target="save">{{ __('common.actions.loading') }}</span>
            </button>
            <a href="{{ route('groceries.index') }}" class="rounded-lg border border-gray-200 px-4 py-2 dark:border-gray-600">
                {{ __('common.actions.cancel') }}
            </a>
        </div>
    </form>
</div>
