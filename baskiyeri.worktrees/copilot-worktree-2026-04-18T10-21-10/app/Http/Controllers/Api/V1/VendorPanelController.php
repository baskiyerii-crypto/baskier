<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\QuoteRequest;
use Illuminate\Http\Request;

class VendorPanelController extends Controller
{
    private function vendor(Request $request)
    {
        $v = $request->user()->vendor;
        if (! $v) {
            abort(403, 'Satıcı hesabı yok.');
        }

        return $v;
    }

    public function dashboard(Request $request)
    {
        $vendor = $this->vendor($request);
        $vid = $vendor->id;

        return response()->json([
            'vendor' => [
                'id' => $vendor->id,
                'name' => $vendor->name,
                'slug' => $vendor->slug,
                'is_active' => (bool) $vendor->is_active,
                'balance' => (string) $vendor->balance,
            ],
            'counts' => [
                'products' => $vendor->products()->count(),
                'orders_total' => Order::where('vendor_id', $vid)->count(),
                'orders_active' => Order::where('vendor_id', $vid)->whereIn('status', ['paid', 'in_progress'])->count(),
                'open_quote_requests' => $this->openQuoteRequestsCount($request, $vendor),
            ],
        ]);
    }

    public function products(Request $request)
    {
        $vendor = $this->vendor($request);
        $products = Product::where('vendor_id', $vendor->id)
            ->with('category')
            ->latest()
            ->paginate(30);

        return response()->json($products);
    }

    public function orders(Request $request)
    {
        $vendor = $this->vendor($request);
        $orders = Order::where('vendor_id', $vendor->id)
            ->with(['user', 'items'])
            ->latest()
            ->paginate(30);

        return response()->json($orders);
    }

    /**
     * Açık teklif talepleri sayısı (satıcının kategorilerine göre).
     */
    private function openQuoteRequestsCount(Request $request, $vendor): int
    {
        $categoryIds = $vendor->quoteCategories()->pluck('categories.id');
        if ($categoryIds->isEmpty()) {
            $categoryIds = $vendor->products()->pluck('category_id')->unique()->filter();
        }
        $q = QuoteRequest::where('status', 'open')
            ->whereIn('category_id', $categoryIds->isEmpty() ? [0] : $categoryIds);
        if ($vendor->city) {
            $q->where(function ($query) use ($vendor) {
                $query->where('city', $vendor->city)->orWhereNull('city');
            });
        }

        return $q->count();
    }
}
