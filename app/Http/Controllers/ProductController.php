<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Review;
use App\Models\CartItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::query()
            ->published()
            ->with(['vendor', 'category', 'variants']);

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
        } elseif ($categorySlug = $request->string('category')->toString()) {
            $query->whereHas('category', fn ($q) => $q->where('slug', $categorySlug));
        }

        if ($request->input('type') === 'digital') {
            $query->where('product_type', 'digital');
        }

        $sort = $request->string('sort')->toString();
        if ($sort === 'price_asc') {
            $query->orderBy('price', 'asc');
        } elseif ($sort === 'price_desc') {
            $query->orderBy('price', 'desc');
        } elseif ($sort === 'rating') {
            $query->withAvg('reviews', 'rating')->orderByDesc('reviews_avg_rating')->latest('id');
        } elseif ($sort === 'fair') {
            // Deterministic vendor seed based on today's day of year
            $daySeed = (int) date('z');
            $query->orderByRaw('(products.vendor_id + ?) % 100', [$daySeed])->latest('id');
        } else {
            // Default newest
            $query->latest('id');
        }

        $products = $query->paginate(20)->withQueryString();

        $categories = Category::where('is_active', true)
            ->whereNull('parent_id')
            ->with('children')
            ->get();

        $selectedCategory = null;
        if ($request->filled('category_id')) {
            $selectedCategory = Category::query()->find((int) $request->input('category_id'));
        } elseif ($request->filled('category')) {
            $selectedCategory = Category::query()->where('slug', $request->string('category')->toString())->first();
        }
        $categoryTrail = $selectedCategory?->ancestorChainIncludingSelf() ?? [];

        return view('products.index', compact('products', 'categories', 'selectedCategory', 'categoryTrail'));
    }

    public function show(string $slug)
    {
        $product = Product::where('slug', $slug)
            ->published()
            ->with(['vendor', 'category.parent', 'variants'])
            ->firstOrFail();

        $related = Product::published()
            ->where('category_id', $product->category_id)
            ->whereKeyNot($product->getKey())
            ->limit(4)
            ->get();
        if ($related->isEmpty()) {
            $related = Product::published()
                ->whereKeyNot($product->getKey())
                ->latest()
                ->limit(4)
                ->get();
        }

        $coPurchasedIds = OrderItem::query()
            ->select('oi2.product_id', DB::raw('COUNT(*) as score'))
            ->from('order_items as oi1')
            ->join('order_items as oi2', 'oi1.order_id', '=', 'oi2.order_id')
            ->where('oi1.product_id', $product->id)
            ->whereNotNull('oi2.product_id')
            ->where('oi2.product_id', '!=', $product->id)
            ->groupBy('oi2.product_id')
            ->orderByDesc('score')
            ->limit(8)
            ->pluck('oi2.product_id')
            ->all();

        $alsoBought = collect();
        if ($coPurchasedIds !== []) {
            $alsoBought = Product::query()
                ->whereIn('id', $coPurchasedIds)
                ->where('is_active', true)
                ->get()
                ->sortBy(fn (Product $p) => array_search($p->id, $coPurchasedIds, true))
                ->values();
        }

        $categoryTrail = $product->category?->ancestorChainIncludingSelf() ?? [];

        $isFavorited = auth()->check()
            && auth()->user()->favorites()->where('product_id', $product->id)->exists();
        $inCartQuantity = 0;
        if (auth()->check()) {
            $inCartQuantity = (int) CartItem::query()
                ->where('user_id', auth()->id())
                ->where('product_id', $product->id)
                ->sum('quantity');
        }

        $productReviewStats = Review::queryForProduct($product->id)
            ->toBase()
            ->selectRaw('ROUND(AVG(rating), 2) as avg_rating, COUNT(*) as reviews_count')
            ->first();

        $productReviews = Review::queryForProduct($product->id)
            ->with('user')
            ->latest()
            ->limit(25)
            ->get();

        return view('products.show', compact(
            'product',
            'related',
            'alsoBought',
            'categoryTrail',
            'isFavorited',
            'inCartQuantity',
            'productReviewStats',
            'productReviews',
        ));
    }
}
