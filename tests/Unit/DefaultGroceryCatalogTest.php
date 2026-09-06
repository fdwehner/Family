<?php

namespace Tests\Unit;

use App\Support\DefaultGroceryCatalog;
use App\Support\GroceryCatalog;
use Tests\TestCase;

class DefaultGroceryCatalogTest extends TestCase
{
    public function test_default_catalog_includes_pictured_staples(): void
    {
        $slugs = DefaultGroceryCatalog::slugs();

        $this->assertContains('milk', $slugs);
        $this->assertContains('coke', $slugs);
        $this->assertContains('eggs', $slugs);

        foreach (DefaultGroceryCatalog::products() as $product) {
            $this->assertFileExists(public_path($product['image_path']));
            $this->assertContains($product['unit'], GroceryCatalog::units());
            $this->assertContains($product['category'], GroceryCatalog::categories());
        }
    }

    public function test_slug_matching_uses_translated_names(): void
    {
        $this->assertContains('milk', DefaultGroceryCatalog::slugsMatching('Milk'));
        $this->assertContains('coke', DefaultGroceryCatalog::slugsMatching('Coke'));

        app()->setLocale('de');
        $this->assertContains('coke', DefaultGroceryCatalog::slugsMatching('Cola'));
    }
}
