<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\DesignApproval;
use App\Models\Order;
use App\Models\Product;
use App\Models\Quote;
use App\Models\QuoteRequest;
use App\Models\QuoteMeetingCharge;
use App\Services\OrderWorkflowService;
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

    public function updateOrderStatus(Request $request, Order $order, OrderWorkflowService $workflow)
    {
        $this->authorize('updateStatus', $order);

        $validated = $request->validate([
            'status' => ['required', 'string', 'max:32'],
        ]);

        try {
            $workflow->transition($order, $validated['status'], $request->user());
        } catch (\InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json($order->fresh()->load(['user', 'items']));
    }

    /**
     * Satıcı paneli için eşleşen (teklif verilebilir) açık talepler.
     * API kullanımı (SPA/mobile) için web paneldeki listelemeye denktir.
     */
    public function matchedQuoteRequests(Request $request)
    {
        $vendor = $this->vendor($request);

        $categoryIds = $vendor->quoteCategories()->pluck('categories.id');
        if ($categoryIds->isEmpty()) {
            $categoryIds = $vendor->products()->pluck('category_id')->unique()->filter();
        }

        $query = QuoteRequest::query()
            ->with(['category', 'user'])
            ->where('status', 'open')
            ->whereIn('category_id', $categoryIds->isEmpty() ? [0] : $categoryIds);

        if ($vendor->city) {
            $query->where(function ($q) use ($vendor) {
                $q->where('city', $vendor->city)->orWhereNull('city');
            });
        }

        $page = $query->latest()->paginate(20);

        $qrIds = collect($page->items())->pluck('id')->all();
        $paid = QuoteMeetingCharge::query()
            ->where('vendor_id', $vendor->id)
            ->whereIn('quote_request_id', $qrIds)
            ->pluck('quote_request_id')
            ->all();
        $myQuotes = Quote::query()
            ->where('vendor_id', $vendor->id)
            ->whereIn('quote_request_id', $qrIds)
            ->get()
            ->keyBy('quote_request_id');

        $paidSet = array_fill_keys($paid, true);
        $page->getCollection()->transform(function ($qr) use ($paidSet, $myQuotes) {
            $qr->has_paid_meeting = isset($paidSet[$qr->id]);
            $qr->my_quote = $myQuotes->get($qr->id);

            return $qr;
        });

        return response()->json($page);
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
