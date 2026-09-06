<?php

namespace App\Models;

use Database\Factories\GroceryItemFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['user_id', 'grocery_product_id', 'quantity', 'is_purchased', 'purchased_at'])]
class GroceryItem extends Model
{
    /** @use HasFactory<GroceryItemFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:2',
            'is_purchased' => 'boolean',
            'purchased_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return BelongsTo<GroceryProduct, $this>
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(GroceryProduct::class, 'grocery_product_id');
    }

    /**
     * @param  Builder<static>  $query
     * @return Builder<static>
     */
    public function scopeForUser(Builder $query, User $user): Builder
    {
        return $query->where('user_id', $user->id);
    }

    public function markPurchased(): void
    {
        $this->forceFill([
            'is_purchased' => true,
            'purchased_at' => now(),
        ])->save();
    }

    public function markUnpurchased(): void
    {
        $this->forceFill([
            'is_purchased' => false,
            'purchased_at' => null,
        ])->save();
    }

    public function displayName(): string
    {
        return $this->product?->displayName() ?? '';
    }

    public function formattedQuantity(): string
    {
        $quantity = (float) $this->quantity;

        if (abs($quantity - round($quantity)) < 0.001) {
            return (string) (int) round($quantity);
        }

        return rtrim(rtrim(number_format($quantity, 2, '.', ''), '0'), '.');
    }
}
