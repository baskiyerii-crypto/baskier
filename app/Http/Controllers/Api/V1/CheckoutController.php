<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Address;
use App\Services\MarketplaceOrderService;
use Illuminate\Http\Request;

class CheckoutController extends Controller
{
    public function __construct(
        private MarketplaceOrderService $orderService
    ) {}

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
            // For now, use same address for shipping & billing in API checkout.
            // Web checkout can pass a separate billing address when available.
            $orders = $this->orderService->createPaidOrdersFromCart($request->user(), $address, $address);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json([
            'message' => 'Sipariş oluşturuldu.',
            'orders' => collect($orders)->map(fn ($o) => [
                'id' => $o->id,
                'order_number' => $o->order_number,
                'subtotal' => $o->subtotal,
            ]),
        ], 201);
    }
}
