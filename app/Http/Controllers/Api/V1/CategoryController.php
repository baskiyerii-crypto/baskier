<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Resources\Api\V1\CategoryResource;
use App\Models\BusinessType;
use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends ApiController
{
    public function index(Request $request)
    {
        $categories = Category::where('is_active', true)
            ->whereNull('parent_id')
            ->with('children')
            ->orderBy('name')
            ->get();

        return $this->ok(CategoryResource::collection($categories));
    }

    public function businessTypes()
    {
        $types = BusinessType::orderBy('sort_order')->get(['id', 'slug', 'name']);

        return $this->ok($types);
    }
}
