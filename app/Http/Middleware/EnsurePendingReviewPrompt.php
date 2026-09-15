<?php

namespace App\Http\Middleware;

use App\Models\Order;
use App\Models\Review;
use Closure;
use Illuminate\Http\Request;

class EnsurePendingReviewPrompt
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();
        if ($user && $user->role === 'customer' && ! $request->routeIs('account.orders.*', 'logout', 'login')) {
            $order = Order::query()
                ->where('user_id', $user->id)
                ->whereIn('status', ['delivered', 'completed'])
                ->whereIn('type', ['quote', 'freelancer', 'product'])
                ->latest()
                ->get()
                ->first(function (Order $o) use ($user) {
                    return ! Review::query()->where('user_id', $user->id)->where('order_id', $o->id)->exists();
                });

            if ($order) {
                view()->share('pendingReviewOrder', $order);
            }
        }

        return $next($request);
    }
}
