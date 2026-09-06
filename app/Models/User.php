<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password', 'grocery_catalog_seeded_at'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'grocery_catalog_seeded_at' => 'datetime',
        ];
    }

    /**
     * @return HasMany<GroceryItem, $this>
     */
    public function groceryItems(): HasMany
    {
        return $this->hasMany(GroceryItem::class);
    }

    /**
     * @return HasMany<GroceryProduct, $this>
     */
    public function groceryProducts(): HasMany
    {
        return $this->hasMany(GroceryProduct::class);
    }
}
