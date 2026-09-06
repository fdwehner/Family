<?php

namespace App\Services;

use App\Models\GroceryProduct;
use App\Models\User;
use App\Support\DefaultGroceryCatalog;

class GroceryProductCatalogService
{
    public function ensureDefaults(User $user): void
    {
        if ($user->grocery_catalog_seeded_at !== null) {
            return;
        }

        foreach (DefaultGroceryCatalog::products() as $product) {
            GroceryProduct::query()->firstOrCreate(
                [
                    'user_id' => $user->id,
                    'slug' => $product['slug'],
                ],
                [
                    'name' => $product['name'],
                    'brand' => $product['brand'],
                    'unit' => $product['unit'],
                    'category' => $product['category'],
                    'image_path' => $product['image_path'],
                    'is_featured' => true,
                    'sort_order' => $product['sort_order'],
                ],
            );
        }

        $user->forceFill([
            'grocery_catalog_seeded_at' => now(),
        ])->save();
    }
}
