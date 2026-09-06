<?php

namespace App\Models;

use App\Support\GroceryCatalog;
use Database\Factories\GroceryItemFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['user_id', 'name', 'quantity', 'unit', 'category', 'notes', 'is_purchased', 'purchased_at'])]
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

    public function categoryLabel(): string
    {
        return GroceryCatalog::categoryLabel($this->category);
    }

    public function unitLabel(): string
    {
        return GroceryCatalog::unitLabel($this->unit);
    }
}
