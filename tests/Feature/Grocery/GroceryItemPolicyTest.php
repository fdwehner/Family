<?php

namespace Tests\Feature\Grocery;

use App\Models\GroceryItem;
use App\Models\User;
use App\Policies\GroceryItemPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GroceryItemPolicyTest extends TestCase
{
    use RefreshDatabase;

    public function test_owners_can_update_and_delete_their_items(): void
    {
        $user = User::factory()->create();
        $item = GroceryItem::factory()->for($user)->create();
        $policy = new GroceryItemPolicy;

        $this->assertTrue($policy->viewAny($user));
        $this->assertTrue($policy->create($user));
        $this->assertTrue($policy->view($user, $item));
        $this->assertTrue($policy->update($user, $item));
        $this->assertTrue($policy->delete($user, $item));
        $this->assertTrue($policy->togglePurchased($user, $item));
    }

    public function test_other_users_cannot_mutate_someone_elses_items(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $item = GroceryItem::factory()->for($owner)->create();
        $policy = new GroceryItemPolicy;

        $this->assertFalse($policy->view($other, $item));
        $this->assertFalse($policy->update($other, $item));
        $this->assertFalse($policy->delete($other, $item));
        $this->assertFalse($policy->togglePurchased($other, $item));
    }
}
