<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Resources\Api\V1\VendorResource;
use App\Models\Vendor;
use Illuminate\Http\Request;

class VendorController extends ApiController
{
    public function index(Request $request)
    {
        $query = Vendor::query()->where('is_active', true)->with('businessTypes');

        if ($type = $request->string('business_type')->toString()) {
            $query->whereHas('businessTypes', fn ($q) => $q->where('slug', $type));
        }

        $vendors = $query->orderBy('name')->paginate(20);
        $vendors->setCollection(
            $vendors->getCollection()->map(
                fn (Vendor $vendor) => (new VendorResource($vendor))->resolve()
            )
        );

        return $this->ok($vendors);
    }

    public function show(string $slug)
    {
        $vendor = Vendor::where('slug', $slug)
            ->where('is_active', true)
            ->with(['businessTypes', 'products' => fn ($q) => $q->published()->limit(50)])
            ->firstOrFail();

        return $this->ok(new VendorResource($vendor));
    }
}
