<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;

class CustomerDashboardController extends Controller
{
    /**
     * Web `hesabim` (müşteri paneli) ile aynı özet sayılar.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $ordersCount = Order::where('user_id', $user->id)->count();
        $cartCount = $user->cartItems()->count();
        $favoritesCount = $user->favorites()->count();

        return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ],
            'orders_count' => $ordersCount,
            'cart_count' => $cartCount,
            'favorites_count' => $favoritesCount,
        ]);
    }
}
