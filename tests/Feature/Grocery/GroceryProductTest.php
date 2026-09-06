<?php

namespace Tests\Feature\Grocery;

use App\Livewire\Forms\GroceryProductForm;
use App\Livewire\GroceryProductsIndex;
use App\Models\GroceryProduct;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class GroceryProductTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_cannot_view_master_data(): void
    {
        $this->get(route('master-data.grocery-products.index'))->assertRedirect(route('login'));
    }

    public function test_users_can_create_a_product_with_brand_unit_category_and_picture(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();
        $photo = UploadedFile::fake()->image('cola.jpg', 80, 80);

        Livewire::actingAs($user)
            ->test(GroceryProductForm::class)
            ->set('name', 'Cola')
            ->set('brand', 'Fizz Co')
            ->set('unit', 'l')
            ->set('category', 'beverages')
            ->set('isFeatured', true)
            ->set('sortOrder', '5')
            ->set('photo', $photo)
            ->call('save')
            ->assertHasNoErrors()
            ->assertRedirect(route('master-data.grocery-products.index'));

        $product = GroceryProduct::query()->where('user_id', $user->id)->where('name', 'Cola')->first();

        $this->assertNotNull($product);
        $this->assertSame('Fizz Co', $product->brand);
        $this->assertSame('l', $product->unit);
        $this->assertSame('beverages', $product->category);
        $this->assertTrue($product->is_featured);
        $this->assertNotNull($product->image_path);
        Storage::disk('public')->assertExists($product->image_path);
    }

    public function test_product_name_is_required(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(GroceryProductForm::class)
            ->set('name', '')
            ->set('category', 'dairy')
            ->set('unit', 'l')
            ->call('save')
            ->assertHasErrors(['name']);
    }

    public function test_users_can_update_their_product(): void
    {
        $user = User::factory()->create();
        $product = GroceryProduct::factory()->for($user)->create([
            'name' => 'Milk',
            'brand' => 'Old Brand',
        ]);

        Livewire::actingAs($user)
            ->test(GroceryProductForm::class, ['groceryProduct' => $product])
            ->set('name', 'Organic milk')
            ->set('brand', 'Dairy Farm')
            ->call('save')
            ->assertHasNoErrors()
            ->assertRedirect(route('master-data.grocery-products.index'));

        $this->assertDatabaseHas('grocery_products', [
            'id' => $product->id,
            'name' => 'Organic milk',
            'brand' => 'Dairy Farm',
        ]);
    }

    public function test_users_cannot_edit_another_users_product(): void
    {
        $user = User::factory()->create();
        $product = GroceryProduct::factory()->create(['name' => 'Secret Coffee']);

        $this->actingAs($user)
            ->get(route('master-data.grocery-products.edit', $product))
            ->assertNotFound();
    }

    public function test_users_can_delete_their_product(): void
    {
        $user = User::factory()->create();
        $product = GroceryProduct::factory()->for($user)->create(['name' => 'Spare Yeast']);

        Livewire::actingAs($user)
            ->test(GroceryProductsIndex::class)
            ->call('deleteProduct', $product->id)
            ->assertHasNoErrors();

        $this->assertDatabaseMissing('grocery_products', ['id' => $product->id]);
    }

    public function test_master_data_search_filters_products(): void
    {
        $user = User::factory()->create();
        GroceryProduct::factory()->for($user)->create(['name' => 'Specialty Honey']);
        GroceryProduct::factory()->for($user)->create(['name' => 'Specialty Vinegar']);

        Livewire::actingAs($user)
            ->test(GroceryProductsIndex::class)
            ->set('search', 'Honey')
            ->assertSee('Specialty Honey', false)
            ->assertDontSee('Specialty Vinegar');
    }
}
