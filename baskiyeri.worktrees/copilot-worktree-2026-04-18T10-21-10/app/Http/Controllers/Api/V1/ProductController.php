<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::query()
            ->where('is_active', true)
            ->with(['vendor', 'category']);

        if ($search = $request->string('q')->toString()) {
            $query->where('name', 'like', "%{$search}%");
        }
        if ($slug = $request->string('category')->toString()) {
            $query->whereHas('category', fn ($q) => $q->where('slug', $slug));
        }
        if ($request->input('type') === 'digital') {
            $query->where('product_type', 'digital');
        }

        $products = $query->latest()->paginate(20);

        return response()->json($products);
    }

    public function show(string $slug)
    {
        $product = Product::where('slug', $slug)
            ->where('is_active', true)
            ->with(['vendor', 'category'])
            ->firstOrFail();

        return response()->json($product);
    }
}
