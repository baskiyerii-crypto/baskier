<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::query()
            ->where('is_active', true)
            ->with(['vendor', 'category'])
            ->latest();

        if ($search = $request->string('q')->toString()) {
            $query->where('name', 'like', "%{$search}%");
        }

        if ($categorySlug = $request->string('category')->toString()) {
            $query->whereHas('category', fn ($q) => $q->where('slug', $categorySlug));
        }

        if ($request->input('type') === 'digital') {
            $query->where('product_type', 'digital');
        }

        $products = $query->paginate(20)->withQueryString();

        $categories = Category::where('is_active', true)
            ->whereNull('parent_id')
            ->with('children')
            ->get();

        return view('products.index', compact('products', 'categories'));
    }

    public function show(string $slug)
    {
        $product = Product::where('slug', $slug)
            ->where('is_active', true)
            ->with(['vendor', 'category'])
            ->firstOrFail();

        $related = Product::where('is_active', true)
            ->where('category_id', $product->category_id)
            ->whereKeyNot($product->getKey())
            ->limit(4)
            ->get();

        $isFavorited = auth()->check()
            && auth()->user()->favorites()->where('product_id', $product->id)->exists();

        return view('products.show', compact('product', 'related', 'isFavorited'));
    }
}
