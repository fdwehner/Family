<?php

namespace Database\Factories;

use App\Models\GroceryProduct;
use App\Models\User;
use App\Support\GroceryCatalog;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<GroceryProduct>
 */
class GroceryProductFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'slug' => null,
            'name' => fake()->unique()->words(2, true),
            'brand' => fake()->optional()->company(),
            'unit' => fake()->randomElement(GroceryCatalog::units()),
            'category' => fake()->randomElement(GroceryCatalog::categories()),
            'image_path' => 'images/groceries/milk.svg',
            'is_featured' => true,
            'sort_order' => fake()->numberBetween(1, 200),
        ];
    }

    public function hiddenFromList(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_featured' => false,
        ]);
    }
}
