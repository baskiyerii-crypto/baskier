<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\Category */
class CategoryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->localizedName(),
            'slug' => $this->slug,
            'description' => $this->localized('description') ?? $this->description,
            'image' => $this->image,
            'parent_id' => $this->parent_id,
            'requires_quote' => $this->requires_quote ?? false,
            'channel' => $this->channel ?? 'physical_quote',
            'termin_days' => $this->termin_days ?? $this->delivery_days,
            'delivery_days' => $this->termin_days ?? $this->delivery_days,
            'children' => CategoryResource::collection($this->whenLoaded('children')),
        ];
    }
}
