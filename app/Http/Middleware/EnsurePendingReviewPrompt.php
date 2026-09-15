<?php

namespace App\Http\Middleware;

use App\Models\Order;
use App\Models\Review;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Throwable;

class EnsurePendingReviewPrompt
{
    public function handle(Request $request, Closure $next)
    {
        try {
            $user = $request->user();
            if ($user && $user->role === 'customer' && ! $request->routeIs('account.orders.*', 'logout', 'login')) {
                $query = Order::query()
                    ->where('user_id', $user->id)
                    ->whereIn('status', ['delivered', 'completed']);

                if (Schema::hasColumn('orders', 'type')) {
                    $query->whereIn('type', ['quote', 'freelancer', 'product']);
                }

                $order = $query->latest()->get()->first(function (Order $o) use ($user) {
                    if (! Schema::hasTable('reviews')) {
                        return false;
                    }

                    return ! Review::query()->where('user_id', $user->id)->where('order_id', $o->id)->exists();
                });

                if ($order) {
                    view()->share('pendingReviewOrder', $order);
                }
            }
        } catch (Throwable) {
            // Panel/vitrin sayfalarını kıracak review prompt hatasını yut.
        }

        return $next($request);
    }
}