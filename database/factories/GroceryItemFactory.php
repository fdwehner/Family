<?php

namespace Database\Factories;

use App\Models\GroceryItem;
use App\Models\GroceryProduct;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<GroceryItem>
 */
class GroceryItemFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'quantity' => fake()->randomElement([1, 2, 3]),
            'is_purchased' => false,
            'purchased_at' => null,
        ];
    }

    public function configure(): static
    {
        return $this->afterMaking(function (GroceryItem $item): void {
            if ($item->grocery_product_id) {
                return;
            }

            $userId = $item->user_id ?: User::factory()->create()->id;
            $item->user_id = $userId;

            $product = GroceryProduct::factory()->create([
                'user_id' => $userId,
            ]);

            $item->grocery_product_id = $product->id;
        });
    }

    public function purchased(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_purchased' => true,
            'purchased_at' => now(),
        ]);
    }
}
