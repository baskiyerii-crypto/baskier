<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Resources\Api\V1\CategoryResource;
use App\Http\Resources\Api\V1\ProductResource;
use App\Models\Category;
use App\Models\FreelancerJobListing;
use App\Models\Product;
use App\Support\FreelancerCategories;
use Illuminate\Http\Request;

/**
 * Web ana sayfası (/) ile aynı veri — mobil uygulama için JSON.
 */
class HomeContentController extends ApiController
{
    public function index(Request $request)
    {
        $featuredCategories = Category::query()
            ->where('is_active', true)
            ->whereNull('parent_id')
            ->limit(6)
            ->get();

        $categories = Category::query()
            ->where('is_active', true)
            ->whereNull('parent_id')
            ->orderBy('name')
            ->get();

        $featuredProducts = app(\App\Services\FairProductDiscoveryService::class)->getDiscoveryProducts(24);

        $digitalProducts = Product::query()
            ->published()
            ->where('product_type', 'digital')
            ->with(['vendor', 'category'])
            ->latest()
            ->limit(8)
            ->get();

        $freelancerJobs = FreelancerJobListing::query()
            ->where('status', 'open')
            ->with('user')
            ->latest()
            ->limit(6)
            ->get();

        $openCounts = FreelancerJobListing::query()
            ->where('status', 'open')
            ->selectRaw('category, count(*) as total')
            ->groupBy('category')
            ->pluck('total', 'category');
        $freelancerCategories = [];
        foreach (FreelancerCategories::catalog() as $key => $config) {
            $freelancerCategories[] = [
                'key' => $key,
                'label' => $config['label'],
                'seed' => $config['seed'],
                'count' => (int) ($openCounts[$key] ?? 0),
            ];
        }

        return $this->ok([
            'featured_categories' => CategoryResource::collection($featuredCategories)->resolve(),
            'categories' => CategoryResource::collection($categories)->resolve(),
            'featured_products' => ProductResource::collection($featuredProducts)->resolve(),
            'discovery_products' => ProductResource::collection($featuredProducts)->resolve(),
            'digital_products' => ProductResource::collection($digitalProducts)->resolve(),
            'freelancer_jobs' => $freelancerJobs,
            'freelancer_categories' => $freelancerCategories,
        ]);
    }
}
