<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\CartItem;
use App\Models\Product;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function index(Request $request)
    {
        $items = CartItem::where('user_id', $request->user()->id)
            ->with(['product.vendor', 'product.category'])
            ->get();

        $total = '0';
        foreach ($items as $item) {
            $total = bcadd($total, bcmul((string) $item->product->price, (string) $item->quantity, 2), 2);
        }

        return response()->json(['items' => $items, 'total' => $total]);
    }

    public function add(Request $request)
    {
        $validated = $request->validate([
            'product_id' => ['required', 'exists:products,id'],
            'quantity' => ['nullable', 'integer', 'min:1', 'max:999'],
        ]);
        $product = Product::findOrFail($validated['product_id']);
        if (! $product->is_active) {
            return response()->json(['message' => 'Ürün satışta değil.'], 422);
        }
        $qty = $validated['quantity'] ?? 1;
        if ($product->stock < $qty) {
            return response()->json(['message' => 'Yetersiz stok.'], 422);
        }

        $row = CartItem::firstOrNew([
            'user_id' => $request->user()->id,
            'product_id' => $product->id,
        ]);
        $newQty = ($row->exists ? $row->quantity : 0) + $qty;
        if ($product->stock < $newQty) {
            return response()->json(['message' => 'Sepet miktarı stoku aşamaz.'], 422);
        }
        $row->quantity = $newQty;
        $row->save();

        return response()->json(['message' => 'Sepete eklendi.', 'item' => $row->load('product')]);
    }

    public function update(Request $request, CartItem $cartItem)
    {
        $this->guard($request, $cartItem);
        $validated = $request->validate([
            'quantity' => ['required', 'integer', 'min:1', 'max:999'],
        ]);
        if ($cartItem->product->stock < $validated['quantity']) {
            return response()->json(['message' => 'Yetersiz stok.'], 422);
        }
        $cartItem->update(['quantity' => $validated['quantity']]);

        return response()->json(['message' => 'Güncellendi.', 'item' => $cartItem->load('product')]);
    }

    public function remove(Request $request, CartItem $cartItem)
    {
        $this->guard($request, $cartItem);
        $cartItem->delete();

        return response()->json(['message' => 'Silindi.']);
    }

    private function guard(Request $request, CartItem $cartItem): void
    {
        if ($cartItem->user_id !== $request->user()->id) {
            abort(403);
        }
    }
}
