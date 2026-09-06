<?php

namespace App\Providers;

use App\Models\GroceryItem;
use App\Models\GroceryProduct;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Route::bind('groceryItem', function (string $value): GroceryItem {
            $user = auth()->user();

            abort_unless($user !== null, 404);

            return GroceryItem::query()
                ->forUser($user)
                ->findOrFail($value);
        });

        Route::bind('groceryProduct', function (string $value): GroceryProduct {
            $user = auth()->user();

            abort_unless($user !== null, 404);

            return GroceryProduct::query()
                ->forUser($user)
                ->findOrFail($value);
        });
    }
}
