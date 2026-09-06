<?php

namespace Tests\Feature\Grocery;

use App\Models\GroceryProduct;
use App\Models\User;
use App\Policies\GroceryProductPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GroceryProductPolicyTest extends TestCase
{
    use RefreshDatabase;

    public function test_owners_can_update_and_delete_their_products(): void
    {
        $user = User::factory()->create();
        $product = GroceryProduct::factory()->for($user)->create();
        $policy = new GroceryProductPolicy;

        $this->assertTrue($policy->viewAny($user));
        $this->assertTrue($policy->create($user));
        $this->assertTrue($policy->view($user, $product));
        $this->assertTrue($policy->update($user, $product));
        $this->assertTrue($policy->delete($user, $product));
    }

    public function test_other_users_cannot_mutate_someone_elses_products(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $product = GroceryProduct::factory()->for($owner)->create();
        $policy = new GroceryProductPolicy;

        $this->assertFalse($policy->view($other, $product));
        $this->assertFalse($policy->update($other, $product));
        $this->assertFalse($policy->delete($other, $product));
    }
}
