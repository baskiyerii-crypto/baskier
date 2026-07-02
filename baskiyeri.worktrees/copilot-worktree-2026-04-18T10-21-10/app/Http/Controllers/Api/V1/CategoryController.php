<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\BusinessType;
use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        $categories = Category::where('is_active', true)
            ->whereNull('parent_id')
            ->with('children')
            ->orderBy('name')
            ->get();

        return response()->json($categories);
    }

    public function businessTypes()
    {
        $types = BusinessType::orderBy('sort_order')->get(['id', 'slug', 'name']);

        return response()->json($types);
    }
}
