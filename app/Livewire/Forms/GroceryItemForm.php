<?php

namespace App\Livewire\Forms;

use App\Models\GroceryItem;
use App\Support\GroceryCatalog;
use App\Traits\FormValidationTrait;
use App\Traits\LogsActivity;
use App\Traits\WithToastNotifications;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Validation\ValidationException;
use Livewire\Component;

class GroceryItemForm extends Component
{
    use AuthorizesRequests;
    use FormValidationTrait;
    use LogsActivity;
    use WithToastNotifications;

    public string $name = '';

    public ?string $quantity = '1';

    public string $unit = 'pcs';

    public string $category = 'other';

    public string $notes = '';

    public ?GroceryItem $groceryItem = null;

    public function mount(?GroceryItem $groceryItem = null): void
    {
        if ($groceryItem?->exists) {
            $this->authorize('update', $groceryItem);
            $this->groceryItem = $groceryItem;
            $this->name = $groceryItem->name;
            $this->quantity = $groceryItem->quantity !== null ? (string) $groceryItem->quantity : '';
            $this->unit = $groceryItem->unit ?? 'pcs';
            $this->category = $groceryItem->category;
            $this->notes = $groceryItem->notes ?? '';

            return;
        }

        $this->authorize('create', GroceryItem::class);
    }

    /**
     * @param  array<string, mixed>  $attributes
     * @return array<string, mixed>
     */
    protected function prepareForValidation($attributes): array
    {
        if (($attributes['quantity'] ?? '') === '') {
            $attributes['quantity'] = null;
        }

        if (($attributes['notes'] ?? '') === '') {
            $attributes['notes'] = null;
        }

        if (($attributes['unit'] ?? '') === '') {
            $attributes['unit'] = null;
        }

        return $attributes;
    }

    /**
     * @return array<string, mixed>
     */
    protected function getValidationRules(): array
    {
        return $this->getValidationService()->getValidationRules('grocery_item');
    }

    /**
     * @return array<string, string>
     */
    protected function getValidationMessages(): array
    {
        return $this->getValidationService()->getValidationMessages('grocery_item');
    }

    public function save(): void
    {
        try {
            if ($this->groceryItem) {
                $this->authorize('update', $this->groceryItem);
            } else {
                $this->authorize('create', GroceryItem::class);
            }

            $validated = $this->validate();
            $payload = [
                'name' => $validated['name'],
                'quantity' => $validated['quantity'] !== null && $validated['quantity'] !== '' ? $validated['quantity'] : null,
                'unit' => $validated['unit'] ?: null,
                'category' => $validated['category'],
                'notes' => $validated['notes'] !== null && $validated['notes'] !== '' ? $validated['notes'] : null,
            ];

            if ($this->groceryItem) {
                $oldValues = $this->groceryItem->only(['name', 'quantity', 'unit', 'category', 'notes']);
                $this->groceryItem->update($payload);
                $this->logCrud('updated', $this->groceryItem, [
                    'old_values' => $oldValues,
                    'new_values' => $this->groceryItem->only(['name', 'quantity', 'unit', 'category', 'notes']),
                ]);
                $this->toastSuccess(__('grocery.messages.updated'));
            } else {
                $item = GroceryItem::create([
                    ...$payload,
                    'user_id' => auth()->id(),
                    'is_purchased' => false,
                ]);
                $this->logCrud('created', $item, [
                    'name' => $item->name,
                ]);
                $this->toastSuccess(__('grocery.messages.created'));
            }

            $this->redirect(route('groceries.index'), navigate: true);
        } catch (ValidationException $exception) {
            throw $exception;
        } catch (\Throwable $exception) {
            $this->logError('Failed to save grocery item', [
                'error' => $exception->getMessage(),
                'grocery_item_id' => $this->groceryItem?->id,
            ]);
            $this->toastError(__('common.messages.error'));
        }
    }

    public function render()
    {
        return view('livewire.forms.grocery-item-form', [
            'categories' => GroceryCatalog::categories(),
            'units' => GroceryCatalog::units(),
            'isEdit' => $this->groceryItem !== null,
        ]);
    }
}
