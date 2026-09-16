<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Vendor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class AdminProductModerationController extends Controller
{
    public function index(Request $request)
    {
        if (! Schema::hasColumn('products', 'moderation_status')) {
            $groups = collect();

            return view('admin.product-approvals.index', compact('groups'));
        }

        $products = Product::query()
            ->with(['vendor', 'category'])
            ->where('moderation_status', Product::MODERATION_PENDING)
            ->latest('submitted_for_moderation_at')
            ->latest('updated_at')
            ->get();

        $groups = $products->groupBy(fn (Product $p) => $p->vendor_id ?: 0);

        return view('admin.product-approvals.index', compact('groups'));
    }

    public function approve(Request $request, Product $product)
    {
        $this->assertPending($product);
        $product->update([
            'moderation_status' => Product::MODERATION_APPROVED,
            'moderation_note' => null,
            'is_active' => true,
        ]);

        return back()->with('success', 'Ürün onaylandı ve yayınlandı.');
    }

    public function reject(Request $request, Product $product)
    {
        $this->assertPending($product);
        $validated = $request->validate([
            'moderation_note' => ['nullable', 'string', 'max:500'],
        ]);
        $product->update([
            'moderation_status' => Product::MODERATION_REJECTED,
            'moderation_note' => $validated['moderation_note'] ?? null,
            'is_active' => false,
        ]);

        return back()->with('success', 'Ürün reddedildi.');
    }

    private function assertPending(Product $product): void
    {
        if (($product->moderation_status ?? null) !== Product::MODERATION_PENDING) {
            abort(422, 'Ürün onay kuyruğunda değil.');
        }
    }
}
