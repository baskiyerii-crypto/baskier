<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\Api\V1\CheckoutStoreRequest;
use App\Http\Resources\Api\V1\OrderResource;
use App\Models\Address;
use App\Services\MarketplaceOrderService;

class CheckoutController extends ApiController
{
    public function __construct(
        private MarketplaceOrderService $orderService
    ) {}

    public function store(CheckoutStoreRequest $request)
    {
        $address = Address::findOrFail($request->validated('address_id'));

        try {
            $orders = $this->orderService->createPaidOrdersFromCart($request->user(), $address, $address);
        } catch (\InvalidArgumentException $e) {
            return $this->fail($e->getMessage(), null, 422);
        }

        return $this->ok(
            OrderResource::collection(collect($orders))->resolve(),
            'Sipariş oluşturuldu.',
            null,
            201
        );
    }
}
