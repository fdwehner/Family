<?php

namespace Tests\Unit;

use App\Support\GroceryCatalog;
use Tests\TestCase;

class GroceryCatalogTest extends TestCase
{
    public function test_categories_and_units_are_stable_lists(): void
    {
        $this->assertContains('dairy', GroceryCatalog::categories());
        $this->assertContains('kg', GroceryCatalog::units());
        $this->assertSame(GroceryCatalog::CATEGORIES, GroceryCatalog::categories());
    }
}
