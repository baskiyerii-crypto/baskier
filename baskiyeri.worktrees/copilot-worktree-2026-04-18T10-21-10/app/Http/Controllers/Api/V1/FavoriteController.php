<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;

class FavoriteController extends Controller
{
    public function index(Request $request)
    {
        $products = $request->user()->favorites()->with(['vendor', 'category'])->paginate(30);

        return response()->json($products);
    }

    public function toggle(Request $request, Product $product)
    {
        $user = $request->user();
        if ($user->favorites()->where('product_id', $product->id)->exists()) {
            $user->favorites()->detach($product->id);
            $favorited = false;
        } else {
            $user->favorites()->attach($product->id);
            $favorited = true;
        }

        return response()->json(['favorited' => $favorited]);
    }
}
