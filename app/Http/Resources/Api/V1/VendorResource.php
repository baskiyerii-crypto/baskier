<?php

namespace App\Http\Resources\Api\V1;

use App\Domain\TrustLevel;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\Vendor */
class VendorResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $trustLevel = (int) ($this->trust_level ?? 0);

        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'company_name' => $this->company_name,
            'city' => $this->city,
            'district' => $this->district,
            'country_code' => $this->country_code,
            'logo' => $this->logo,
            'description' => $this->description,
            'rating_average' => $this->rating_average,
            'reviews_count' => $this->reviews_count,
            'is_active' => $this->is_active,
            'verification_status' => $this->verification_status ?? 'unverified',
            'trust_level' => $trustLevel,
            'trust_badge_label' => TrustLevel::badgeName($trustLevel),
            'trust_level_label' => TrustLevel::label($trustLevel),
            'is_suspended' => (bool) ($this->is_suspended ?? false),
            'business_types' => $this->whenLoaded('businessTypes'),
            'products' => ProductResource::collection($this->whenLoaded('products')),
        ];
    }
}
