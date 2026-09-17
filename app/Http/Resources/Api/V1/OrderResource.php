<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\Order */
class OrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $subtotal = (string) ($this->subtotal ?? '0.00');
        $tax = (string) ($this->tax_amount ?? '0.00');
        $shipping = (string) ($this->shipping_amount ?? '0.00');
        $total = bcadd(bcadd($subtotal, $tax, 2), $shipping, 2);

        return [
            'id' => $this->id,
            'order_number' => $this->order_number,
            'status' => $this->status,
            'payment_status' => $this->payment_status ?? 'pending',
            'type' => $this->type,
            'currency' => $this->currency ?? 'TRY',
            'subtotal' => $subtotal,
            'tax_amount' => $tax,
            'shipping_amount' => $shipping,
            'total_amount' => $total,
            'vendor_id' => $this->vendor_id,
            'user_id' => $this->user_id,
            'contractor_user_id' => $this->contractor_user_id,
            'shipping_address' => $this->shipping_address,
            'tracking_number' => $this->tracking_number,
            'carrier_code' => $this->carrier_code,
            'shipping_label_path' => $this->shipping_label_path,
            'created_at' => $this->created_at,
            'paid_at' => $this->paid_at,
            'shipped_at' => $this->shipped_at,
            'delivered_at' => $this->delivered_at,
            'vendor' => $this->whenLoaded('vendor', fn () => new VendorResource($this->vendor)),
            'items' => $this->whenLoaded('items', fn () => $this->items->map(fn ($item) => [
                'id' => $item->id,
                'name' => $item->name,
                'price' => (string) $item->price,
                'quantity' => (int) $item->quantity,
                'product_id' => $item->product_id,
                'variant_id' => $item->variant_id,
                'product' => $item->relationLoaded('product') && $item->product ? new ProductResource($item->product) : null,
            ])),
            'design_approvals' => $this->whenLoaded('designApprovals'),
        ];
    }
}
