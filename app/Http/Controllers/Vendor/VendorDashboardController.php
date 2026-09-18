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
        if ($vendor->prefersOutdoorPanel() && \Illuminate\Support\Facades\Route::has('outdoor-panel.dashboard')) {
            return redirect()->route('outdoor-panel.dashboard');
        }
        $vendor->load('businessTypes');
        $productsCount = $vendor->products()->count();
        $products = $vendor->products()->with('category')->latest()->limit(10)->get();

        $ordersCount = Order::where('vendor_id', $vendor->id)->count();

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

        $upcomingPayouts = Order::where('vendor_id', $vendor->id)
            ->whereNotNull('commission_ready_at')
            ->where('commission_ready_at', '<=', now()->addDays(14))
            ->where('payout_approved', false)
            ->sum('vendor_amount');

        $moduleEnds = collect([
            'freelancer' => $vendor->freelancer_expires_at,
            'quotes' => $vendor->quotes_expires_at,
            'tabela' => $vendor->tabela_expires_at,
            'ozalit' => $vendor->ozalit_expires_at,
        ])->filter(fn ($d) => $d && $d->isFuture() && $d->lte(now()->addDays(14)));

        $pendingContractsCount = 0;
        if (\Illuminate\Support\Facades\Schema::hasTable('contract_vendor_acceptances')) {
            $pendingContractsCount = \App\Models\ContractVendorAcceptance::query()
                ->where('vendor_id', $vendor->id)
                ->whereIn('status', ['pending', 'expired'])
                ->count();
        }

        $revenueTrend = ['labels' => [], 'values' => []];
        for ($i = 29; $i >= 0; $i--) {
            $day = now()->subDays($i)->toDateString();
            $revenueTrend['labels'][] = $day;
            $revenueTrend['values'][] = (float) Order::where('vendor_id', $vendor->id)
                ->whereDate('created_at', $day)
                ->whereNotIn('status', [OrderStatus::CANCELLED])
                ->sum('vendor_amount');
        }

        $statusBreakdown = Order::where('vendor_id', $vendor->id)
            ->selectRaw('status, COUNT(*) as c')
            ->groupBy('status')
            ->pluck('c', 'status');

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
            'openQuoteRequestsCount',
            'upcomingPayouts',
            'moduleEnds',
            'pendingContractsCount',
            'revenueTrend',
            'statusBreakdown'
        ));
    }
}
