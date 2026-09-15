<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\Api\V1\CartAddRequest;
use App\Http\Requests\Api\V1\CartUpdateRequest;
use App\Models\CartItem;
use App\Models\Product;

class CartController extends ApiController
{
    public function index(\Illuminate\Http\Request $request)
    {
        $items = CartItem::where('user_id', $request->user()->id)
            ->with(['product.vendor', 'product.category'])
            ->get();

        $total = '0';
        foreach ($items as $item) {
            $total = bcadd($total, bcmul((string) $item->product->price, (string) $item->quantity, 2), 2);
        }

        return $this->ok(['items' => $items, 'total' => $total]);
    }

    public function add(CartAddRequest $request)
    {
        $validated = $request->validated();
        $product = Product::findOrFail($validated['product_id']);
        if (! $product->is_active) {
            return $this->fail('Ürün satışta değil.', null, 422);
        }
        $qty = $validated['quantity'] ?? 1;
        if ($product->stock < $qty) {
            return $this->fail('Yetersiz stok.', null, 422);
        }

        $row = CartItem::firstOrNew([
            'user_id' => $request->user()->id,
            'product_id' => $product->id,
        ]);
        $newQty = ($row->exists ? $row->quantity : 0) + $qty;
        if ($product->stock < $newQty) {
            return $this->fail('Sepet miktarı stoku aşamaz.', null, 422);
        }
        $row->quantity = $newQty;
        $row->save();

        return $this->ok(['item' => $row->load('product')], 'Sepete eklendi.');
    }

    public function update(CartUpdateRequest $request, CartItem $cartItem)
    {
        $validated = $request->validated();
        if ($cartItem->product->stock < $validated['quantity']) {
            return $this->fail('Yetersiz stok.', null, 422);
        }
        $cartItem->update(['quantity' => $validated['quantity']]);

        return $this->ok(['item' => $cartItem->load('product')], 'Güncellendi.');
    }

    public function remove(\Illuminate\Http\Request $request, CartItem $cartItem)
    {
        if ($cartItem->user_id !== $request->user()->id) {
            abort(403);
        }
        $cartItem->delete();

        return $this->ok(['deleted' => true], 'Silindi.');
    }
}
