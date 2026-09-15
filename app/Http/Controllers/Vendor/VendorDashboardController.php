<?php

namespace App\Http\Controllers\Vendor;

use App\Domain\OrderStatus;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\QuoteRequest;
use Illuminate\Http\Request;

class VendorDashboardController extends Controller
{
    public function index(Request $request)
    {
        $vendor = $request->user()->vendor;
        if (! $vendor) {
            abort(403, 'Satıcı hesabı bulunamadı.');
        }
        $vendor->load('businessTypes');
        $productsCount = $vendor->products()->count();
        $products = $vendor->products()->with('category')->latest()->limit(10)->get();

        $ordersCount = Order::where('vendor_id', $vendor->id)->count();

        // Aktif siparişler (onaylandı, prova, üretimde, kargoda vb.)
        $ordersPending = Order::where('vendor_id', $vendor->id)
            ->whereIn('status', [
                OrderStatus::CONFIRMED,
                OrderStatus::DESIGN_REVIEW,
                OrderStatus::IN_PRODUCTION,
                OrderStatus::READY_TO_SHIP,
                OrderStatus::SHIPPED,
            ])->count();

        $proofPendingCount = Order::where('vendor_id', $vendor->id)
            ->where('status', OrderStatus::DESIGN_REVIEW)
            ->count();

        $readyToShipCount = Order::where('vendor_id', $vendor->id)
            ->whereIn('status', [OrderStatus::IN_PRODUCTION, OrderStatus::READY_TO_SHIP])
            ->count();

        $totalRevenue = (float) Order::where('vendor_id', $vendor->id)
            ->whereNotIn('status', [OrderStatus::CANCELLED, OrderStatus::PENDING])
            ->sum('vendor_amount');

        $recentOrders = Order::where('vendor_id', $vendor->id)
            ->with(['user', 'items.product'])
            ->latest()
            ->limit(5)
            ->get();

        $openQuoteRequestsCount = 0;
        if ($vendor->hasActiveQuotesModule()) {
            $categoryIds = $vendor->quoteCategories()->pluck('categories.id');
            if ($categoryIds->isEmpty()) {
                $categoryIds = $vendor->products()->pluck('category_id')->unique()->filter();
            }
            $openQuotesQuery = QuoteRequest::where('status', 'open')->whereIn('category_id', $categoryIds->isEmpty() ? [0] : $categoryIds);
            if ($vendor->city) {
                $openQuotesQuery->where(function ($q) use ($vendor) {
                    $q->where('city', $vendor->city)->orWhereNull('city');
                });
            }
            $openQuoteRequestsCount = $openQuotesQuery->count();
        }

        return view('vendor.dashboard', compact(
            'vendor',
            'productsCount',
            'products',
            'ordersCount',
            'ordersPending',
            'proofPendingCount',
            'readyToShipCount',
            'totalRevenue',
            'recentOrders',
            'openQuoteRequestsCount'
        ));
    }
}
