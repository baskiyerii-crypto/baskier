<?php

namespace App\Policies;

use App\Models\Product;
use App\Models\User;

class ProductPolicy
{
    public function view(?User $user, Product $product): bool
    {
        return (bool) $product->is_active
            || ($user && $user->isAdmin())
            || ($user && $user->vendor_id && (int) $product->vendor_id === (int) $user->vendor_id);
    }

    public function update(User $user, Product $product): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return $user->vendor_id && (int) $product->vendor_id === (int) $user->vendor_id;
    }

    public function delete(User $user, Product $product): bool
    {
        return $this->update($user, $product);
    }
}
