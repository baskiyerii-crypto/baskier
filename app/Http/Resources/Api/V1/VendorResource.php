<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\Vendor */
class VendorResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'company_name' => $this->company_name,
            'city' => $this->city,
            'district' => $this->district,
            'logo' => $this->logo,
            'description' => $this->description,
            'rating_average' => $this->rating_average,
            'reviews_count' => $this->reviews_count,
            'is_active' => $this->is_active,
            'business_types' => $this->whenLoaded('businessTypes'),
            'products' => ProductResource::collection($this->whenLoaded('products')),
        ];
    }
}
