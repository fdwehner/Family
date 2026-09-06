<?php

namespace App\Support;

final class DefaultGroceryCatalog
{
    /**
     * Staple products shown on the shopping list for new households.
     *
     * @return list<array{slug: string, name: string, brand: ?string, unit: string, category: string, image_path: string, sort_order: int}>
     */
    public static function products(): array
    {
        $items = [
            ['slug' => 'milk', 'name' => 'Milk', 'unit' => 'l', 'category' => 'dairy'],
            ['slug' => 'coke', 'name' => 'Coke', 'unit' => 'l', 'category' => 'beverages'],
            ['slug' => 'eggs', 'name' => 'Eggs', 'unit' => 'pcs', 'category' => 'dairy'],
            ['slug' => 'bread', 'name' => 'Bread', 'unit' => 'pcs', 'category' => 'bakery'],
            ['slug' => 'butter', 'name' => 'Butter', 'unit' => 'pack', 'category' => 'dairy'],
            ['slug' => 'cheese', 'name' => 'Cheese', 'unit' => 'pack', 'category' => 'dairy'],
            ['slug' => 'yogurt', 'name' => 'Yogurt', 'unit' => 'pcs', 'category' => 'dairy'],
            ['slug' => 'water', 'name' => 'Water', 'unit' => 'l', 'category' => 'beverages'],
            ['slug' => 'juice', 'name' => 'Juice', 'unit' => 'l', 'category' => 'beverages'],
            ['slug' => 'coffee', 'name' => 'Coffee', 'unit' => 'pack', 'category' => 'beverages'],
            ['slug' => 'apples', 'name' => 'Apples', 'unit' => 'kg', 'category' => 'produce'],
            ['slug' => 'bananas', 'name' => 'Bananas', 'unit' => 'kg', 'category' => 'produce'],
            ['slug' => 'tomatoes', 'name' => 'Tomatoes', 'unit' => 'kg', 'category' => 'produce'],
            ['slug' => 'potatoes', 'name' => 'Potatoes', 'unit' => 'kg', 'category' => 'produce'],
            ['slug' => 'onions', 'name' => 'Onions', 'unit' => 'kg', 'category' => 'produce'],
            ['slug' => 'chicken', 'name' => 'Chicken', 'unit' => 'kg', 'category' => 'meat'],
            ['slug' => 'pasta', 'name' => 'Pasta', 'unit' => 'pack', 'category' => 'pantry'],
            ['slug' => 'rice', 'name' => 'Rice', 'unit' => 'kg', 'category' => 'pantry'],
            ['slug' => 'cereal', 'name' => 'Cereal', 'unit' => 'pack', 'category' => 'pantry'],
            ['slug' => 'sugar', 'name' => 'Sugar', 'unit' => 'kg', 'category' => 'pantry'],
            ['slug' => 'flour', 'name' => 'Flour', 'unit' => 'kg', 'category' => 'pantry'],
            ['slug' => 'salt', 'name' => 'Salt', 'unit' => 'pack', 'category' => 'pantry'],
            ['slug' => 'oil', 'name' => 'Oil', 'unit' => 'l', 'category' => 'pantry'],
        ];

        $products = [];

        foreach ($items as $index => $item) {
            $products[] = [
                'slug' => $item['slug'],
                'name' => $item['name'],
                'brand' => null,
                'unit' => $item['unit'],
                'category' => $item['category'],
                'image_path' => 'images/groceries/'.$item['slug'].'.svg',
                'sort_order' => ($index + 1) * 10,
            ];
        }

        return $products;
    }

    /**
     * @return list<string>
     */
    public static function slugs(): array
    {
        return array_map(static fn (array $product): string => $product['slug'], self::products());
    }

    /**
     * @return list<string>
     */
    public static function slugsMatching(string $search): array
    {
        $needle = mb_strtolower(trim($search));

        if ($needle === '') {
            return [];
        }

        $matches = [];

        foreach (self::slugs() as $slug) {
            $translated = mb_strtolower((string) __('grocery.catalog.'.$slug));
            $name = mb_strtolower((string) collect(self::products())->firstWhere('slug', $slug)['name'] ?? $slug);

            if (str_contains($translated, $needle) || str_contains($name, $needle) || str_contains($slug, $needle)) {
                $matches[] = $slug;
            }
        }

        return $matches;
    }
}
