<?php

namespace Database\Factories;

use App\Models\GroceryItem;
use App\Models\User;
use App\Support\GroceryCatalog;
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
            'name' => fake()->words(2, true),
            'quantity' => fake()->randomElement([1, 2, 3, 0.5, 1.5]),
            'unit' => fake()->randomElement(GroceryCatalog::units()),
            'category' => fake()->randomElement(GroceryCatalog::categories()),
            'notes' => fake()->optional()->sentence(),
            'is_purchased' => false,
            'purchased_at' => null,
        ];
    }

    public function purchased(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_purchased' => true,
            'purchased_at' => now(),
        ]);
    }
}
