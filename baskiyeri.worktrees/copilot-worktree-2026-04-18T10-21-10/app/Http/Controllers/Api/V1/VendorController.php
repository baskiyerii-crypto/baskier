<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Vendor;
use Illuminate\Http\Request;

class VendorController extends Controller
{
    public function index(Request $request)
    {
        $query = Vendor::query()->where('is_active', true)->with('businessTypes');

        if ($type = $request->string('business_type')->toString()) {
            $query->whereHas('businessTypes', fn ($q) => $q->where('slug', $type));
        }

        $vendors = $query->orderBy('name')->paginate(20);

        return response()->json($vendors);
    }

    public function show(string $slug)
    {
        $vendor = Vendor::where('slug', $slug)
            ->where('is_active', true)
            ->with(['businessTypes', 'products' => fn ($q) => $q->where('is_active', true)->limit(50)])
            ->firstOrFail();

        return response()->json($vendor);
    }
}
