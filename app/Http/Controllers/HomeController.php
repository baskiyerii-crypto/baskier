<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Support\FreelancerCategories;

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
            ->published()
            ->where('is_featured', true)
            ->with(['vendor', 'category', 'images'])
            ->latest()
            ->limit(24)
            ->get();

        $digitalProducts = Product::query()
            ->published()
            ->where('product_type', 'digital')
            ->with(['vendor', 'category'])
            ->latest()
            ->limit(8)
            ->get();

        $freelancerCategories = [];
        foreach (FreelancerCategories::catalog() as $key => $config) {
            $freelancerCategories[] = [
                'key' => $key,
                'label' => $config['label'],
                'seed' => $config['seed'],
                'count' => 0,
            ];
        }

        return view('home.index', compact(
            'featuredCategories',
            'categories',
            'featuredProducts',
            'digitalProducts',
            'freelancerCategories',
        ));
    }
}
