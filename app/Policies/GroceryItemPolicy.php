<?php

namespace App\Policies;

use App\Models\GroceryItem;
use App\Models\User;

class GroceryItemPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, GroceryItem $groceryItem): bool
    {
        return $user->id === $groceryItem->user_id;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, GroceryItem $groceryItem): bool
    {
        return $user->id === $groceryItem->user_id;
    }

    public function delete(User $user, GroceryItem $groceryItem): bool
    {
        return $user->id === $groceryItem->user_id;
    }

    public function togglePurchased(User $user, GroceryItem $groceryItem): bool
    {
        return $this->update($user, $groceryItem);
    }

    public function clearPurchased(User $user): bool
    {
        return true;
    }
}
