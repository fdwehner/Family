<?php

namespace Tests\Feature\Grocery;

use App\Livewire\Forms\GroceryItemForm;
use App\Livewire\GroceryItemsIndex;
use App\Models\GroceryItem;
use App\Models\User;
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

    public function test_users_can_view_only_their_own_items(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();

        GroceryItem::factory()->for($user)->create(['name' => 'Milk']);
        GroceryItem::factory()->for($other)->create(['name' => 'Secret Coffee']);

        $this->actingAs($user)
            ->get(route('groceries.index'))
            ->assertOk()
            ->assertSee('Milk', false)
            ->assertDontSee('Secret Coffee');
    }

    public function test_users_can_create_a_grocery_item(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(GroceryItemForm::class)
            ->set('name', 'Milk')
            ->set('quantity', '2')
            ->set('unit', 'l')
            ->set('category', 'dairy')
            ->set('notes', 'Organic')
            ->call('save')
            ->assertHasNoErrors()
            ->assertRedirect(route('groceries.index'));

        $this->assertDatabaseHas('grocery_items', [
            'user_id' => $user->id,
            'name' => 'Milk',
            'category' => 'dairy',
            'is_purchased' => false,
        ]);
    }

    public function test_item_name_is_required(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(GroceryItemForm::class)
            ->set('name', '')
            ->set('category', 'dairy')
            ->call('save')
            ->assertHasErrors(['name']);
    }

    public function test_users_can_update_their_item(): void
    {
        $user = User::factory()->create();
        $item = GroceryItem::factory()->for($user)->create(['name' => 'Milk']);

        Livewire::actingAs($user)
            ->test(GroceryItemForm::class, ['groceryItem' => $item])
            ->set('name', 'Oat milk')
            ->call('save')
            ->assertHasNoErrors()
            ->assertRedirect(route('groceries.index'));

        $this->assertDatabaseHas('grocery_items', [
            'id' => $item->id,
            'name' => 'Oat milk',
        ]);
    }

    public function test_users_cannot_edit_another_users_item(): void
    {
        $user = User::factory()->create();
        $item = GroceryItem::factory()->create(['name' => 'Secret Coffee']);

        $this->actingAs($user)
            ->get(route('groceries.edit', $item))
            ->assertNotFound();
    }

    public function test_users_can_toggle_purchased_and_delete_items(): void
    {
        $user = User::factory()->create();
        $item = GroceryItem::factory()->for($user)->create(['name' => 'Bread']);

        Livewire::actingAs($user)
            ->test(GroceryItemsIndex::class)
            ->call('togglePurchased', $item->id)
            ->assertHasNoErrors();

        $this->assertTrue($item->fresh()->is_purchased);

        Livewire::actingAs($user)
            ->test(GroceryItemsIndex::class)
            ->call('deleteItem', $item->id)
            ->assertHasNoErrors();

        $this->assertDatabaseMissing('grocery_items', ['id' => $item->id]);
    }

    public function test_search_filters_the_list(): void
    {
        $user = User::factory()->create();
        GroceryItem::factory()->for($user)->create(['name' => 'Milk']);
        GroceryItem::factory()->for($user)->create(['name' => 'Apples']);

        Livewire::actingAs($user)
            ->test(GroceryItemsIndex::class)
            ->set('search', 'Milk')
            ->assertSee('Milk', false)
            ->assertDontSee('Apples');
    }

    public function test_clearing_purchased_items_only_removes_the_current_users_purchased_rows(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();

        $purchased = GroceryItem::factory()->for($user)->purchased()->create(['name' => 'Butter']);
        $needed = GroceryItem::factory()->for($user)->create(['name' => 'Eggs']);
        $otherPurchased = GroceryItem::factory()->for($other)->purchased()->create(['name' => 'Juice']);

        Livewire::actingAs($user)
            ->test(GroceryItemsIndex::class)
            ->call('clearPurchased')
            ->assertHasNoErrors();

        $this->assertDatabaseMissing('grocery_items', ['id' => $purchased->id]);
        $this->assertDatabaseHas('grocery_items', ['id' => $needed->id]);
        $this->assertDatabaseHas('grocery_items', ['id' => $otherPurchased->id]);
    }
}
