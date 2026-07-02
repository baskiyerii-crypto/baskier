<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Review;
use App\Support\ReviewModeration;
use Illuminate\Http\Request;

class CustomerReviewController extends Controller
{
    public function store(Request $request, Order $order)
    {
        if ($order->user_id !== $request->user()->id) {
            abort(403);
        }
        if (! in_array($order->status, ['delivered', 'completed'], true)) {
            return back()->with('error', 'Değerlendirme yalnızca teslim edilen siparişler için yapılabilir.');
        }
        if (Review::where('order_id', $order->id)->exists()) {
            return back()->with('error', 'Bu sipariş zaten değerlendirilmiş.');
        }

        $validated = $request->validate([
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'comment' => ['nullable', 'string', 'max:2000'],
            'product_id' => ['nullable', 'exists:products,id'],
        ]);

        if (ReviewModeration::commentContainsBlockedLanguage($validated['comment'] ?? null)) {
            return back()->with('error', 'Yorumunuz uygun görünmüyor (spam veya uygunsuz ifade). Lütfen metni düzenleyin.');
        }

        if (! empty($validated['product_id'])) {
            $inOrder = $order->items()->where('product_id', $validated['product_id'])->exists();
            if (! $inOrder) {
                return back()->with('error', 'Seçilen ürün bu siparişte yok.');
            }
        }

        Review::create([
            'user_id' => $request->user()->id,
            'vendor_id' => $order->vendor_id,
            'order_id' => $order->id,
            'product_id' => $validated['product_id'] ?? $order->items->first()?->product_id,
            'rating' => $validated['rating'],
            'comment' => $validated['comment'] ?? null,
        ]);

        return back()->with('success', 'Değerlendirmeniz kaydedildi. Teşekkürler.');
    }
}
