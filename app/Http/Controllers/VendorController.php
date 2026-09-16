<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Vendor;
use Illuminate\Http\Request;

class VendorController extends Controller
{
    public function index(Request $request)
    {
        $query = Vendor::where('is_active', true)->with('businessTypes');

        if ($request->filled('business_type')) {
            $query->whereHas('businessTypes', fn ($q) => $q->where('slug', $request->business_type));
        }

        $vendors = $query->orderBy('name')->paginate(12)->withQueryString();
        $businessTypes = \App\Models\BusinessType::orderBy('sort_order')->get();

        return view('vendors.index', compact('vendors', 'businessTypes'));
    }

    public function show(string $slug)
    {
        $vendor = Vendor::where('slug', $slug)
            ->where('is_active', true)
            ->with('businessTypes')
            ->firstOrFail();

        $products = Product::where('vendor_id', $vendor->id)
            ->published()
            ->with('category')
            ->paginate(12);

        return view('vendors.show', compact('vendor', 'products'));
    }
}
