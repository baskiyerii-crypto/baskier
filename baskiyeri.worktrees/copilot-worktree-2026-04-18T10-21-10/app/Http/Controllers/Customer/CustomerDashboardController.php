<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;

class CustomerDashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $ordersCount = Order::where('user_id', $user->id)->count();
        $cartCount = $user->cartItems()->count();
        $favoritesCount = $user->favorites()->count();

        return view('customer.dashboard', compact('user', 'ordersCount', 'cartCount', 'favoritesCount'));
    }
}
