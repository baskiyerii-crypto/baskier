<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\FreelancerJobListing;
use App\Models\Product;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index()
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

        // Armut tarzı: hizmet türü kartları – kategori bazlı açık ilan sayıları
        $freelancerCategoryKeys = [
            'logo'       => ['label' => 'Logo & Kurumsal Kimlik', 'seed' => 'flogo'],
            'brochure'   => ['label' => 'Broşür & Katalog', 'seed' => 'fbrochure'],
            'digital'    => ['label' => 'Dijital İçerik & Sosyal Medya', 'seed' => 'fdigital'],
            'wordpress'  => ['label' => 'Web Sitesi & WordPress', 'seed' => 'fwordpress'],
            'other'      => ['label' => 'Tabela & Diğer İşler', 'seed' => 'fother'],
        ];
        $openCounts = FreelancerJobListing::query()
            ->where('status', 'open')
            ->selectRaw('category, count(*) as total')
            ->groupBy('category')
            ->pluck('total', 'category');
        $freelancerCategories = [];
        foreach ($freelancerCategoryKeys as $key => $config) {
            $freelancerCategories[] = [
                'key'   => $key,
                'label' => $config['label'],
                'seed'  => $config['seed'],
                'count' => (int) ($openCounts[$key] ?? 0),
            ];
        }

        return view('home.index', compact(
            'featuredCategories',
            'categories',
            'featuredProducts',
            'digitalProducts',
            'freelancerJobs',
            'freelancerCategories',
        ));
    }
}
