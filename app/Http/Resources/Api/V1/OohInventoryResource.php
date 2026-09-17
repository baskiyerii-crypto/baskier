<?php

namespace App\Http\Resources\Api\V1;

use App\Support\MediaUrl;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\OohInventory */
class OohInventoryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $showContact = false;
        if ($request->user() && $this->relationLoaded('vendor')) {
            $showContact = (int) $request->user()->vendor_id === (int) $this->vendor_id
                || $request->user()->isAdmin();
        }

        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'description' => $this->description,
            'city' => $this->city,
            'district' => $this->district,
            'address' => $this->address,
            'lat' => $this->lat,
            'lng' => $this->lng,
            'list_price' => $this->list_price,
            'price_unit' => $this->price_unit,
            'status' => $this->status,
            'cover' => MediaUrl::public($this->coverPath()),
            'images' => $this->whenLoaded('images', fn () => $this->images->map(fn ($img) => MediaUrl::public($img->path))->values()),
            'vendor' => $this->whenLoaded('vendor', fn () => [
                'id' => $this->vendor->id,
                'name' => $this->vendor->name,
                'slug' => $this->vendor->slug,
                'phone' => $showContact ? $this->vendor->phone : null,
                'email' => $showContact ? $this->vendor->email : null,
            ]),
            'category' => $this->whenLoaded('category', fn () => [
                'id' => $this->category?->id,
                'name' => $this->category?->name,
            ]),
        ];
    }
}
