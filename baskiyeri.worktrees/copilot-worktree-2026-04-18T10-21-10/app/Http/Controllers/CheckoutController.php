<?php

namespace App\Http\Controllers;

use App\Models\Address;
use App\Services\MarketplaceOrderService;
use Illuminate\Http\Request;

class CheckoutController extends Controller
{
    public function __construct(
        private MarketplaceOrderService $orderService
    ) {}

    public function index(Request $request)
    {
        $user = $request->user();
        $items = $user->cartItems()->with(['product.vendor', 'product.category'])->get();
        if ($items->isEmpty()) {
            return redirect()->route('cart.index')->with('info', 'Sepetiniz boş.');
        }
        $total = '0';
        foreach ($items as $item) {
            $total = bcadd($total, bcmul((string) $item->product->price, (string) $item->quantity, 2), 2);
        }
        $addresses = $user->addresses()->orderByDesc('is_default')->get();

        return view('checkout.index', compact('items', 'total', 'addresses'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'address_id' => ['required', 'exists:addresses,id'],
        ]);
        $address = Address::findOrFail($validated['address_id']);
        if ($address->user_id !== $request->user()->id) {
            abort(403);
        }

        try {
            $orders = $this->orderService->createPaidOrdersFromCart($request->user(), $address);
        } catch (\InvalidArgumentException $e) {
            return redirect()->route('cart.index')->with('error', $e->getMessage());
        }

        $first = $orders[0]->order_number;
        $msg = count($orders) > 1
            ? count($orders) . ' sipariş oluşturuldu. İlk sipariş no: #' . $first
            : 'Siparişiniz alındı. Sipariş no: #' . $first;

        return redirect()->route('customer.orders.index')->with('success', $msg);
    }
}
