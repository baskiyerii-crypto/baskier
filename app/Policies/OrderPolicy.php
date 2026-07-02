<?php

namespace App\Policies;

use App\Models\Order;
use App\Models\User;

class OrderPolicy
{
    public function view(User $user, Order $order): bool
    {
        if ($user->isAdmin()) {
            return true;
        }
        if ($order->user_id === $user->id) {
            return true;
        }
        if ($user->vendor_id && (int) $order->vendor_id === (int) $user->vendor_id) {
            return true;
        }

        return false;
    }

    public function updateStatus(User $user, Order $order): bool
    {
        return $user->vendor_id && (int) $order->vendor_id === (int) $user->vendor_id;
    }

    public function review(User $user, Order $order): bool
    {
        return $order->user_id === $user->id;
    }

    public function designAct(User $user, Order $order): bool
    {
        return $user->vendor_id && (int) $order->vendor_id === (int) $user->vendor_id;
    }

    public function designRespond(User $user, Order $order): bool
    {
        return $order->user_id === $user->id;
    }
}
