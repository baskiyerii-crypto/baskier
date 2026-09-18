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
            'country_code' => $this->country_code,
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
            'insight' => $this->whenLoaded('insight', fn () => $this->insight ? [
                'population_province' => $this->insight->population_province,
                'population_district' => $this->insight->population_district,
                'population_year' => $this->insight->population_year,
                'road_class' => $this->insight->road_class,
                'road_name' => $this->insight->road_name,
                'road_ref' => $this->insight->road_ref,
                'vehicle_aadt' => $this->insight->vehicle_aadt,
                'vehicle_aadt_year' => $this->insight->vehicle_aadt_year,
                'vehicle_source' => $this->insight->vehicle_source,
                'pedestrian_kind' => $this->insight->pedestrian_kind,
                'visibility_band' => $this->insight->visibility_band,
                'street_view_available' => $this->insight->street_view_available,
            ] : null),
        ];
    }
}
