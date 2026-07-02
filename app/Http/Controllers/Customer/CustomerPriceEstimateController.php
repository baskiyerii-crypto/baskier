<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Category;

class CustomerPriceEstimateController extends Controller
{
    public function index()
    {
        $parents = Category::query()
            ->where('is_active', true)
            ->whereNull('parent_id')
            ->with(['children' => fn ($q) => $q->where('is_active', true)->orderBy('name')])
            ->orderBy('name')
            ->get();

        $categoryOptions = [];
        foreach ($parents as $p) {
            $categoryOptions[] = ['id' => $p->id, 'label' => $p->name];
            foreach ($p->children as $c) {
                $categoryOptions[] = ['id' => $c->id, 'label' => $p->name.' › '.$c->name];
            }
        }

        return view('customer.price-estimate', [
            'categoryOptions' => $categoryOptions,
            'apiBase' => url('/api/v1'),
        ]);
    }
}
