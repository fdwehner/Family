<?php

namespace App\Support;

final class GroceryCatalog
{
    /**
     * @var list<string>
     */
    public const CATEGORIES = [
        'produce',
        'dairy',
        'meat',
        'bakery',
        'pantry',
        'frozen',
        'beverages',
        'household',
        'other',
    ];

    /**
     * @var list<string>
     */
    public const UNITS = [
        'pcs',
        'kg',
        'g',
        'l',
        'ml',
        'pack',
    ];

    /**
     * @return list<string>
     */
    public static function categories(): array
    {
        return self::CATEGORIES;
    }

    /**
     * @return list<string>
     */
    public static function units(): array
    {
        return self::UNITS;
    }

    public static function categoryLabel(string $category): string
    {
        return __('grocery.categories.'.$category);
    }

    public static function unitLabel(?string $unit): string
    {
        if ($unit === null || $unit === '') {
            return '';
        }

        return __('grocery.units.'.$unit);
    }
}
