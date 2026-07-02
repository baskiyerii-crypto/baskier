<?php

namespace App\Http\Controllers;

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

        return view('cart.index', compact('items', 'total'));
    }

    public function add(Request $request, Product $product)
    {
        if (! $product->is_active) {
            return back()->with('error', 'Bu ürün satışta değil.');
        }
        $validated = $request->validate([
            'quantity' => ['nullable', 'integer', 'min:1', 'max:999'],
        ]);
        $qty = $validated['quantity'] ?? 1;
        if ($product->stock < $qty) {
            return back()->with('error', 'Yeterli stok yok.');
        }

        $row = CartItem::firstOrNew([
            'user_id' => $request->user()->id,
            'product_id' => $product->id,
        ]);
        $newQty = ($row->exists ? $row->quantity : 0) + $qty;
        if ($product->stock < $newQty) {
            return back()->with('error', 'Sepetteki miktar stoku aşamaz.');
        }
        $row->quantity = $newQty;
        $row->save();

        return back()->with('success', 'Ürün sepete eklendi.');
    }

    public function update(Request $request, CartItem $cartItem)
    {
        $this->authorizeCartItem($request, $cartItem);
        $validated = $request->validate([
            'quantity' => ['required', 'integer', 'min:1', 'max:999'],
        ]);
        if ($cartItem->product->stock < $validated['quantity']) {
            return back()->with('error', 'Yeterli stok yok.');
        }
        $cartItem->update(['quantity' => $validated['quantity']]);

        return back()->with('success', 'Sepet güncellendi.');
    }

    public function remove(Request $request, CartItem $cartItem)
    {
        $this->authorizeCartItem($request, $cartItem);
        $cartItem->delete();

        return back()->with('success', 'Ürün sepetten çıkarıldı.');
    }

    private function authorizeCartItem(Request $request, CartItem $cartItem): void
    {
        if ($cartItem->user_id !== $request->user()->id) {
            abort(403);
        }
    }
}
