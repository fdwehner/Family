<?php

namespace App\Policies;

use App\Models\GroceryProduct;
use App\Models\User;

class GroceryProductPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, GroceryProduct $groceryProduct): bool
    {
        return $user->id === $groceryProduct->user_id;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, GroceryProduct $groceryProduct): bool
    {
        return $user->id === $groceryProduct->user_id;
    }

    public function delete(User $user, GroceryProduct $groceryProduct): bool
    {
        return $user->id === $groceryProduct->user_id;
    }
}
