<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Category;
use App\Models\Product;
use App\Services\PricingEstimateService;
use Illuminate\Http\Request;

class ProductController extends ApiController
{
    public function index(Request $request)
    {
        $query = Product::query()
            ->where('is_active', true)
            ->with(['vendor', 'category']);

        if ($search = $request->string('q')->toString()) {
            $term = '%'.addcslashes($search, '%_\\').'%';
            $query->where(function ($q) use ($term) {
                $q->where('name', 'like', $term)
                    ->orWhere('short_description', 'like', $term)
                    ->orWhere('description', 'like', $term)
                    ->orWhere('sku', 'like', $term)
                    ->orWhereHas('category', fn ($cq) => $cq->where('name', 'like', $term));
            });
        }

        if ($request->filled('category_id')) {
            $family = Category::familyIdsIncludingSelf((int) $request->input('category_id'));
            if ($family !== []) {
                $query->whereIn('category_id', $family);
            }
        } elseif ($slug = $request->string('category')->toString()) {
            $query->whereHas('category', fn ($q) => $q->where('slug', $slug));
        }
        if ($request->input('type') === 'digital') {
            $query->where('product_type', 'digital');
        }

        $products = $query->latest()->paginate(20);

        return $this->ok($products);
    }

    public function show(string $slug)
    {
        $product = Product::where('slug', $slug)
            ->where('is_active', true)
            ->with(['vendor', 'category'])
            ->firstOrFail();

        return $this->ok($product);
    }

    public function priceEstimate(string $slug, PricingEstimateService $pricing)
    {
        $product = Product::where('slug', $slug)
            ->where('is_active', true)
            ->with(['vendor', 'category'])
            ->firstOrFail();

        return $this->ok($pricing->estimateForProduct($product));
    }
}
