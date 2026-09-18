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
        $openOrdersCount = Order::where('user_id', $user->id)->whereNotIn('status', ['cancelled', 'completed'])->count();
        $cartCount = $user->cartItems()->sum('quantity');
        $favoritesCount = $user->favorites()->count();
        $openQuotesCount = \Illuminate\Support\Facades\Schema::hasTable('quote_requests')
            ? \App\Models\QuoteRequest::query()->where('user_id', $user->id)->where('status', 'open')->count()
            : 0;
        $badges = app(\App\Services\PanelNavBadgeService::class)->forUser($user);
        $unreadMessages = (int) ($badges['customer_messages'] ?? 0);

        return view('customer.dashboard', compact('user', 'ordersCount', 'cartCount', 'favoritesCount', 'openOrdersCount', 'openQuotesCount', 'unreadMessages'));
    }
}
