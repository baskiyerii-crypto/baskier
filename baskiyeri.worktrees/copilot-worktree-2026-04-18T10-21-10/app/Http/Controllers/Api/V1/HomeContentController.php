<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\FreelancerJobListing;
use App\Models\Product;
use Illuminate\Http\Request;

/**
 * Web ana sayfası (/) ile aynı veri — mobil uygulama için JSON.
 */
class HomeContentController extends Controller
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

        $featuredProducts = Product::query()
            ->where('is_active', true)
            ->where('is_featured', true)
            ->with(['vendor', 'category'])
            ->limit(50)
            ->get();

        $digitalProducts = Product::query()
            ->where('is_active', true)
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

        $freelancerCategoryKeys = [
            'logo' => ['label' => 'Logo & Kurumsal Kimlik', 'seed' => 'flogo'],
            'brochure' => ['label' => 'Broşür & Katalog', 'seed' => 'fbrochure'],
            'digital' => ['label' => 'Dijital İçerik & Sosyal Medya', 'seed' => 'fdigital'],
            'wordpress' => ['label' => 'Web Sitesi & WordPress', 'seed' => 'fwordpress'],
            'other' => ['label' => 'Tabela & Diğer İşler', 'seed' => 'fother'],
        ];
        $openCounts = FreelancerJobListing::query()
            ->where('status', 'open')
            ->selectRaw('category, count(*) as total')
            ->groupBy('category')
            ->pluck('total', 'category');
        $freelancerCategories = [];
        foreach ($freelancerCategoryKeys as $key => $config) {
            $freelancerCategories[] = [
                'key' => $key,
                'label' => $config['label'],
                'seed' => $config['seed'],
                'count' => (int) ($openCounts[$key] ?? 0),
            ];
        }

        return response()->json([
            'featured_categories' => $featuredCategories,
            'categories' => $categories,
            'featured_products' => $featuredProducts,
            'digital_products' => $digitalProducts,
            'freelancer_jobs' => $freelancerJobs,
            'freelancer_categories' => $freelancerCategories,
        ]);
    }
}
