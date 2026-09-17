<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\Api\V1\CheckoutStoreRequest;
use App\Http\Resources\Api\V1\OrderResource;
use App\Services\CheckoutService;

class CheckoutController extends ApiController
{
    public function __construct(
        private CheckoutService $checkoutService
    ) {}

    public function store(CheckoutStoreRequest $request)
    {
        $user = $request->user();
        $validated = $request->validated();

        $data = [
            'shipping_address_id' => (int) $validated['address_id'],
            'billing_address_id' => ! empty($validated['billing_address_id']) ? (int) $validated['billing_address_id'] : null,
            'use_shipping_for_billing' => empty($validated['billing_address_id']),
            'payment_method' => $validated['payment_method'] ?? 'credit_card',
            'idempotency_key' => $validated['idempotency_key'] ?? $request->header('X-Idempotency-Key'),
            'invoice_type' => $validated['invoice_type'] ?? 'individual',
            'invoice_full_name' => $validated['invoice_full_name'] ?? $user->name,
            'invoice_email' => $validated['invoice_email'] ?? $user->email,
            'invoice_phone' => $validated['invoice_phone'] ?? ($user->phone ?: '5550000000'),
        ];

        $result = $this->checkoutService->processCheckout($user, $data);

        if (! $result['success']) {
            return $this->fail($result['error'] ?? 'Ödeme işlemi başlatılamadı.', null, 422);
        }

        $orders = $result['orders'] ?? [];

        return $this->ok(
            [
                'orders' => OrderResource::collection(collect($orders))->resolve(),
                'requires_action' => $result['requires_action'] ?? false,
                'payment_page_url' => $result['payment_page_url'] ?? null,
                'checkout_form_content' => $result['checkout_form_content'] ?? null,
                'attempt_id' => $result['attempt']?->id ?? null,
            ],
            'Sipariş oluşturuldu. Ödeme bekleniyor.',
            null,
            201
        );
    }
}
