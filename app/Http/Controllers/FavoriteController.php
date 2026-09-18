<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class FavoriteController extends Controller
{
    public function index(Request $request)
    {
        $products = $request->user()->favorites()->with(['vendor', 'category'])->paginate(20);

        return view('favorites.index', compact('products'));
    }

    public function toggle(Request $request, Product $product)
    {
        $user = $request->user();
        if ($user->favorites()->where('product_id', $product->id)->exists()) {
            $user->favorites()->detach($product->id);
            $msg = 'Favorilerden çıkarıldı.';
        } else {
            $user->favorites()->attach($product->id);
            $msg = 'Favorilere eklendi.';
        }

        $favorited = $user->favorites()->where('product_id', $product->id)->exists();
        $count = $user->favorites()->count();

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => $msg,
                'favorited' => $favorited,
                'count' => $count,
            ]);
        }

        return back()->with('success', $msg);
    }
}
