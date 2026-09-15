<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\Order */
class OrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'order_number' => $this->order_number,
            'subtotal' => $this->subtotal,
            'status' => $this->status,
            'type' => $this->type,
            'vendor_id' => $this->vendor_id,
            'created_at' => $this->created_at,
        ];
    }
}
