<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\Product */
class ProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'vendor_id' => $this->vendor_id,
            'category_id' => $this->category_id,
            'name' => $this->localizedName(),
            'slug' => $this->slug,
            'sku' => $this->sku,
            'main_image' => $this->main_image,
            'price' => $this->price,
            'stock' => $this->stock,
            'is_featured' => (bool) $this->is_featured,
            'is_active' => $this->is_active,
            'is_quote_based' => $this->isQuoteBased(),
            'has_variants' => $this->hasVariants(),
            'cta_type' => $this->isQuoteBased()
                ? 'quote'
                : ((int) $this->stock <= 0 ? 'out_of_stock' : ($this->hasVariants() ? 'variant' : 'add_to_cart')),
            'short_description' => $this->localized('short_description') ?? $this->short_description,
            'description' => $this->localized('description') ?? $this->description,
            'product_type' => $this->product_type,
            'pricing_type' => $this->pricing_type,
            'price_min' => $this->price_min,
            'price_max' => $this->price_max,
            'category' => $this->whenLoaded('category', fn () => new CategoryResource($this->category)),
            'vendor' => $this->whenLoaded('vendor', fn () => new VendorResource($this->vendor)),
            'variants' => $this->whenLoaded('variants', fn () => $this->variants->map(fn ($v) => [
                'id' => $v->id,
                'name' => $v->name,
                'price' => bcadd((string) $this->price, (string) $v->price_adjustment, 2),
                'stock' => (int) $v->stock,
            ])),
        ];
    }
}
