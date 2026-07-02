<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class VendorProductController extends ApiController
{
    private function vendorId(Request $request): int
    {
        $vendor = $request->user()->vendor;
        if (! $vendor) {
            abort(403, 'Satıcı hesabı yok.');
        }

        return (int) $vendor->id;
    }

    public function index(Request $request)
    {
        $vendorId = $this->vendorId($request);

        $q = Product::query()
            ->where('vendor_id', $vendorId)
            ->with('category')
            ->latest();

        if ($request->filled('status')) {
            $status = $request->string('status')->toString();
            if ($status === 'active') {
                $q->where('is_active', true);
            } elseif ($status === 'inactive') {
                $q->where('is_active', false);
            }
        }

        return $this->ok($q->paginate(30));
    }

    public function store(Request $request)
    {
        $vendorId = $this->vendorId($request);

        $validated = $request->validate([
            'category_id' => ['required', 'exists:categories,id'],
            'name' => ['required', 'string', 'max:255'],
            'sku' => ['nullable', 'string', 'max:64'],
            'price' => ['required', 'numeric', 'min:0'],
            'stock' => ['nullable', 'integer', 'min:0'],
            'product_type' => ['nullable', 'in:physical,digital'],
            'digital_link' => ['nullable', 'string', 'max:2000'],
            'catalog_type' => ['nullable', 'string', 'max:32'],
            'pricing_type' => ['nullable', 'string', 'max:32'],
            'price_min' => ['nullable', 'numeric', 'min:0'],
            'price_max' => ['nullable', 'numeric', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
            'is_featured' => ['nullable', 'boolean'],
            'short_description' => ['nullable', 'string', 'max:500'],
            'description' => ['nullable', 'string', 'max:5000'],
        ]);

        $product = Product::create(array_merge($validated, [
            'vendor_id' => $vendorId,
            'slug' => Str::slug($validated['name']),
            'stock' => $validated['stock'] ?? 0,
            'product_type' => $validated['product_type'] ?? 'physical',
            'catalog_type' => $validated['catalog_type'] ?? 'standard',
            'pricing_type' => $validated['pricing_type'] ?? 'fixed',
            'is_active' => (bool) ($validated['is_active'] ?? true),
            'is_featured' => (bool) ($validated['is_featured'] ?? false),
        ]));

        // Ensure uniqueness-ish without heavy logic.
        $product->update(['slug' => $product->slug.'-'.$product->id]);

        return $this->ok($product->fresh()->load('category'), 'Ürün oluşturuldu.', null, 201);
    }

    public function update(Request $request, Product $product)
    {
        $vendorId = $this->vendorId($request);
        if ((int) $product->vendor_id !== $vendorId) {
            return $this->fail('Bu ürün size ait değil.', null, 403);
        }

        $validated = $request->validate([
            'category_id' => ['sometimes', 'exists:categories,id'],
            'name' => ['sometimes', 'string', 'max:255'],
            'sku' => ['nullable', 'string', 'max:64'],
            'price' => ['sometimes', 'numeric', 'min:0'],
            'stock' => ['nullable', 'integer', 'min:0'],
            'product_type' => ['nullable', 'in:physical,digital'],
            'digital_link' => ['nullable', 'string', 'max:2000'],
            'catalog_type' => ['nullable', 'string', 'max:32'],
            'pricing_type' => ['nullable', 'string', 'max:32'],
            'price_min' => ['nullable', 'numeric', 'min:0'],
            'price_max' => ['nullable', 'numeric', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
            'is_featured' => ['nullable', 'boolean'],
            'short_description' => ['nullable', 'string', 'max:500'],
            'description' => ['nullable', 'string', 'max:5000'],
        ]);

        if (array_key_exists('name', $validated)) {
            $validated['slug'] = Str::slug($validated['name']).'-'.$product->id;
        }

        $product->fill($validated)->save();

        return $this->ok($product->fresh()->load('category'), 'Ürün güncellendi.');
    }

    public function destroy(Request $request, Product $product)
    {
        $vendorId = $this->vendorId($request);
        if ((int) $product->vendor_id !== $vendorId) {
            return $this->fail('Bu ürün size ait değil.', null, 403);
        }

        $product->delete();

        return $this->ok(null, 'Ürün silindi.');
    }
}

