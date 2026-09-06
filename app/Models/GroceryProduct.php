<?php

namespace App\Models;

use App\Support\GroceryCatalog;
use Database\Factories\GroceryProductFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

#[Fillable([
    'user_id',
    'slug',
    'name',
    'brand',
    'unit',
    'category',
    'image_path',
    'is_featured',
    'sort_order',
])]
class GroceryProduct extends Model
{
    /** @use HasFactory<GroceryProductFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_featured' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::deleting(function (GroceryProduct $product): void {
            $product->deleteStoredImage();
        });
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return HasMany<GroceryItem, $this>
     */
    public function groceryItems(): HasMany
    {
        return $this->hasMany(GroceryItem::class);
    }

    /**
     * @param  Builder<static>  $query
     * @return Builder<static>
     */
    public function scopeForUser(Builder $query, User $user): Builder
    {
        return $query->where('user_id', $user->id);
    }

    public function displayName(): string
    {
        if ($this->slug) {
            $key = 'grocery.catalog.'.$this->slug;
            $translated = __($key);

            if ($translated !== $key) {
                return $translated;
            }
        }

        return $this->name;
    }

    public function categoryLabel(): string
    {
        return GroceryCatalog::categoryLabel($this->category);
    }

    public function unitLabel(): string
    {
        return GroceryCatalog::unitLabel($this->unit);
    }

    public function imageUrl(): ?string
    {
        if ($this->image_path === null || $this->image_path === '') {
            return null;
        }

        if ($this->usesBundledImage()) {
            return asset($this->image_path);
        }

        return Storage::disk('public')->url($this->image_path);
    }

    public function usesBundledImage(): bool
    {
        return is_string($this->image_path) && str_starts_with($this->image_path, 'images/');
    }

    public function deleteStoredImage(): void
    {
        if ($this->image_path === null || $this->usesBundledImage()) {
            return;
        }

        Storage::disk('public')->delete($this->image_path);
    }
}
