<?php

namespace Tests\Feature\Grocery;

use App\Livewire\GroceryItemsIndex;
use App\Models\GroceryItem;
use App\Models\GroceryProduct;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class GroceryItemTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_cannot_view_the_grocery_list(): void
    {
        $this->get(route('groceries.index'))->assertRedirect(route('login'));
    }

    public function test_users_see_pictured_staples_and_not_other_users_products(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();

        GroceryProduct::factory()->for($other)->create([
            'name' => 'Secret Coffee',
            'is_featured' => true,
        ]);

        $this->actingAs($user)
            ->get(route('groceries.index'))
            ->assertOk()
            ->assertSee(__('grocery.catalog.milk'), false)
            ->assertSee(__('grocery.catalog.coke'), false)
            ->assertSee(__('grocery.catalog.eggs'), false)
            ->assertDontSee('Secret Coffee');
    }

    public function test_plus_adds_a_product_to_the_list_and_minus_removes_it(): void
    {
        $user = User::factory()->create();

        $component = Livewire::actingAs($user)->test(GroceryItemsIndex::class);
        $milk = GroceryProduct::query()
            ->forUser($user)
            ->where('slug', 'milk')
            ->firstOrFail();

        $component->call('incrementProduct', $milk->id)->assertHasNoErrors();

        $this->assertDatabaseHas('grocery_items', [
            'user_id' => $user->id,
            'grocery_product_id' => $milk->id,
            'quantity' => 1,
            'is_purchased' => false,
        ]);

        $component->call('incrementProduct', $milk->id)->assertHasNoErrors();
        $this->assertEquals(2, (float) GroceryItem::query()->forUser($user)->where('grocery_product_id', $milk->id)->value('quantity'));

        $component->call('decrementProduct', $milk->id)->assertHasNoErrors();
        $component->call('decrementProduct', $milk->id)->assertHasNoErrors();

        $this->assertDatabaseMissing('grocery_items', [
            'user_id' => $user->id,
            'grocery_product_id' => $milk->id,
        ]);
    }

    public function test_users_cannot_add_another_users_product_to_their_list(): void
    {
        $user = User::factory()->create();
        $product = GroceryProduct::factory()->create();

        $this->expectException(ModelNotFoundException::class);

        Livewire::actingAs($user)
            ->test(GroceryItemsIndex::class)
            ->call('incrementProduct', $product->id);
    }

    public function test_users_can_toggle_purchased_and_clear_purchased_items(): void
    {
        $user = User::factory()->create();
        $item = GroceryItem::factory()->for($user)->create();

        Livewire::actingAs($user)
            ->test(GroceryItemsIndex::class)
            ->call('togglePurchased', $item->id)
            ->assertHasNoErrors();

        $this->assertTrue($item->fresh()->is_purchased);

        $needed = GroceryItem::factory()->for($user)->create();
        $other = User::factory()->create();
        $otherPurchased = GroceryItem::factory()->for($other)->purchased()->create();

        Livewire::actingAs($user)
            ->test(GroceryItemsIndex::class)
            ->call('clearPurchased')
            ->assertHasNoErrors();

        $this->assertDatabaseMissing('grocery_items', ['id' => $item->id]);
        $this->assertDatabaseHas('grocery_items', ['id' => $needed->id]);
        $this->assertDatabaseHas('grocery_items', ['id' => $otherPurchased->id]);
    }

    public function test_search_filters_the_pictured_catalog(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(GroceryItemsIndex::class)
            ->set('search', 'Milk')
            ->assertSee(__('grocery.catalog.milk'), false)
            ->assertDontSee(__('grocery.catalog.apples'), false);
    }

    public function test_hidden_products_are_not_shown_on_the_list(): void
    {
        $user = User::factory()->create();
        GroceryProduct::factory()->for($user)->hiddenFromList()->create([
            'name' => 'Hidden Spice Blend',
        ]);

        Livewire::actingAs($user)
            ->test(GroceryItemsIndex::class)
            ->assertDontSee('Hidden Spice Blend');
    }
}
