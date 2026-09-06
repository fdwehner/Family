<?php

namespace App\Livewire\Forms;

use App\Models\GroceryProduct;
use App\Support\GroceryCatalog;
use App\Traits\FormValidationTrait;
use App\Traits\LogsActivity;
use App\Traits\WithToastNotifications;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Validation\ValidationException;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;

class GroceryProductForm extends Component
{
    use AuthorizesRequests;
    use FormValidationTrait;
    use LogsActivity;
    use WithFileUploads;
    use WithToastNotifications;

    public string $name = '';

    public string $brand = '';

    public string $unit = 'pcs';

    public string $category = 'other';

    public mixed $photo = null;

    public bool $isFeatured = true;

    public string $sortOrder = '100';

    public ?GroceryProduct $groceryProduct = null;

    public function mount(?GroceryProduct $groceryProduct = null): void
    {
        if ($groceryProduct?->exists) {
            $this->authorize('update', $groceryProduct);
            $this->groceryProduct = $groceryProduct;
            $this->name = $groceryProduct->name;
            $this->brand = $groceryProduct->brand ?? '';
            $this->unit = $groceryProduct->unit;
            $this->category = $groceryProduct->category;
            $this->isFeatured = $groceryProduct->is_featured;
            $this->sortOrder = (string) $groceryProduct->sort_order;

            return;
        }

        $this->authorize('create', GroceryProduct::class);
    }

    /**
     * @param  array<string, mixed>  $attributes
     * @return array<string, mixed>
     */
    protected function prepareForValidation($attributes): array
    {
        if (($attributes['brand'] ?? '') === '') {
            $attributes['brand'] = null;
        }

        if (! ($attributes['photo'] ?? null) instanceof TemporaryUploadedFile) {
            $attributes['photo'] = null;
        }

        return $attributes;
    }

    /**
     * @return array<string, mixed>
     */
    protected function getValidationRules(): array
    {
        return $this->getValidationService()->getValidationRules('grocery_product');
    }

    /**
     * @return array<string, string>
     */
    protected function getValidationMessages(): array
    {
        return $this->getValidationService()->getValidationMessages('grocery_product');
    }

    public function save(): void
    {
        try {
            if ($this->groceryProduct) {
                $this->authorize('update', $this->groceryProduct);
            } else {
                $this->authorize('create', GroceryProduct::class);
            }

            $validated = $this->validate();
            $payload = [
                'name' => $validated['name'],
                'brand' => $validated['brand'] !== null && $validated['brand'] !== '' ? $validated['brand'] : null,
                'unit' => $validated['unit'],
                'category' => $validated['category'],
                'is_featured' => (bool) $validated['isFeatured'],
                'sort_order' => (int) $validated['sortOrder'],
            ];

            if (($validated['photo'] ?? null) instanceof TemporaryUploadedFile) {
                if ($this->groceryProduct) {
                    $this->groceryProduct->deleteStoredImage();
                }

                $payload['image_path'] = $validated['photo']->store('grocery-products/'.auth()->id(), 'public');
            }

            if ($this->groceryProduct) {
                $oldValues = $this->groceryProduct->only(['name', 'brand', 'unit', 'category', 'is_featured', 'sort_order', 'image_path']);
                $this->groceryProduct->update($payload);
                $this->logCrud('updated', $this->groceryProduct, [
                    'old_values' => $oldValues,
                    'new_values' => $this->groceryProduct->only(['name', 'brand', 'unit', 'category', 'is_featured', 'sort_order', 'image_path']),
                ]);
                $this->toastSuccess(__('grocery.master_data.messages.updated'));
            } else {
                $product = GroceryProduct::query()->create([
                    ...$payload,
                    'user_id' => auth()->id(),
                    'slug' => null,
                ]);
                $this->logCrud('created', $product, [
                    'name' => $product->name,
                ]);
                $this->toastSuccess(__('grocery.master_data.messages.created'));
            }

            $this->redirect(route('master-data.grocery-products.index'), navigate: true);
        } catch (ValidationException $exception) {
            throw $exception;
        } catch (\Throwable $exception) {
            $this->logError('Failed to save grocery product', [
                'error' => $exception->getMessage(),
                'grocery_product_id' => $this->groceryProduct?->id,
            ]);
            $this->toastError(__('common.messages.error'));
        }
    }

    public function render()
    {
        return view('livewire.forms.grocery-product-form', [
            'categories' => GroceryCatalog::categories(),
            'units' => GroceryCatalog::units(),
            'isEdit' => $this->groceryProduct !== null,
        ]);
    }
}
