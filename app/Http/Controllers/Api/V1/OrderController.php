<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Review;
use App\Support\ReviewModeration;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $orders = Order::where('user_id', $request->user()->id)
            ->with(['vendor', 'items', 'contractor'])
            ->latest()
            ->paginate(20);

        return response()->json($orders);
    }

    public function show(Request $request, Order $order)
    {
        if ($order->user_id !== $request->user()->id) {
            abort(403);
        }
        $order->load(['vendor', 'items.product', 'contractor', 'quote']);

        return response()->json($order);
    }

    public function review(Request $request, Order $order)
    {
        if ($order->user_id !== $request->user()->id) {
            abort(403);
        }
        if (! in_array($order->status, ['delivered', 'completed'], true)) {
            return response()->json(['message' => 'Bu sipariş için değerlendirme yapılamaz.'], 422);
        }
        if (Review::where('order_id', $order->id)->exists()) {
            return response()->json(['message' => 'Zaten değerlendirildi.'], 422);
        }

        $validated = $request->validate([
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'comment' => ['nullable', 'string', 'max:2000'],
            'product_id' => ['nullable', 'exists:products,id'],
        ]);

        if (ReviewModeration::commentContainsBlockedLanguage($validated['comment'] ?? null)) {
            return response()->json(['message' => 'Yorum uygun değil.'], 422);
        }

        if (! empty($validated['product_id'])) {
            $inOrder = $order->items()->where('product_id', $validated['product_id'])->exists();
            if (! $inOrder) {
                return response()->json(['message' => 'Ürün bu siparişe ait değil.'], 422);
            }
        }

        $review = Review::create([
            'user_id' => $request->user()->id,
            'vendor_id' => $order->vendor_id,
            'order_id' => $order->id,
            'product_id' => $validated['product_id'] ?? $order->items->first()?->product_id,
            'rating' => $validated['rating'],
            'comment' => $validated['comment'] ?? null,
        ]);

        return response()->json($review, 201);
    }
}
