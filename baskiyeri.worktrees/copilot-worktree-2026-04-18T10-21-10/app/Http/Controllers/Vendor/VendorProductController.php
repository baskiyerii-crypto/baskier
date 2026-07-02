<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Imports\ProductsImport;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Facades\Excel;

class VendorProductController extends Controller
{
    private function getVendor(Request $request)
    {
        $vendor = $request->user()->vendor;
        if (! $vendor) {
            abort(403, 'Satıcı hesabı bulunamadı.');
        }
        return $vendor;
    }

    public function index(Request $request)
    {
        $vendor = $this->getVendor($request);
        $products = $vendor->products()->with('category')->latest()->paginate(20);
        return view('vendor.products.index', compact('vendor', 'products'));
    }

    public function create(Request $request)
    {
        $this->getVendor($request);
        $categories = Category::where('is_active', true)->orderBy('name')->get();
        return view('vendor.products.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $vendor = $this->getVendor($request);
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'category_id' => ['required', 'exists:categories,id'],
            'price' => ['required', 'numeric', 'min:0'],
            'stock' => ['required', 'integer', 'min:0'],
            'short_description' => ['nullable', 'string'],
            'product_type' => ['nullable', 'in:physical,digital'],
            'digital_link' => ['nullable', 'url', 'max:500'],
            'main_image' => ['nullable', 'image', 'max:2048'],
        ]);
        $validated['vendor_id'] = $vendor->id;
        $validated['slug'] = \Illuminate\Support\Str::slug($validated['name']) . '-' . time();
        $validated['is_active'] = true;
        $validated['product_type'] = $validated['product_type'] ?? 'physical';
        if ($request->hasFile('main_image')) {
            $validated['main_image'] = $request->file('main_image')->store('products', 'public');
        }
        if (($validated['product_type'] ?? '') === 'digital') {
            $validated['digital_link'] = $request->digital_link;
        }
        Product::create($validated);
        return redirect()->route('vendor.products.index')->with('success', 'Ürün eklendi.');
    }

    public function edit(Request $request, Product $product)
    {
        $vendor = $this->getVendor($request);
        if ($product->vendor_id !== $vendor->id) {
            abort(403, 'Bu ürün size ait değil.');
        }
        $categories = Category::where('is_active', true)->orderBy('name')->get();
        return view('vendor.products.edit', compact('product', 'categories'));
    }

    public function update(Request $request, Product $product)
    {
        $vendor = $this->getVendor($request);
        if ($product->vendor_id !== $vendor->id) {
            abort(403, 'Bu ürün size ait değil.');
        }
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'category_id' => ['required', 'exists:categories,id'],
            'price' => ['required', 'numeric', 'min:0'],
            'stock' => ['required', 'integer', 'min:0'],
            'short_description' => ['nullable', 'string'],
            'is_active' => ['boolean'],
            'product_type' => ['nullable', 'in:physical,digital'],
            'digital_link' => ['nullable', 'url', 'max:500'],
            'main_image' => ['nullable', 'image', 'max:2048'],
        ]);
        $validated['slug'] = \Illuminate\Support\Str::slug($validated['name']) . '-' . $product->id;
        $validated['is_active'] = $request->boolean('is_active');
        $validated['product_type'] = $validated['product_type'] ?? 'physical';
        if ($request->hasFile('main_image')) {
            if ($product->main_image) {
                Storage::disk('public')->delete($product->main_image);
            }
            $validated['main_image'] = $request->file('main_image')->store('products', 'public');
        }
        if (($validated['product_type'] ?? '') === 'digital') {
            $validated['digital_link'] = $request->digital_link;
        }
        $product->update($validated);
        return redirect()->route('vendor.products.index')->with('success', 'Ürün güncellendi.');
    }

    public function destroy(Request $request, Product $product)
    {
        $vendor = $this->getVendor($request);
        if ($product->vendor_id !== $vendor->id) {
            abort(403, 'Bu ürün size ait değil.');
        }
        $product->delete();
        return redirect()->route('vendor.products.index')->with('success', 'Ürün silindi.');
    }

    public function downloadTemplate(Request $request)
    {
        $this->getVendor($request);

        return Excel::download(new class implements FromArray {
            public function array(): array
            {
                return [
                    ['name', 'category_id', 'price', 'stock', 'sku', 'short_description', 'product_type'],
                    ['Örnek ürün', 1, 99.90, 10, 'SKU-1', 'Kısa açıklama', 'physical'],
                ];
            }
        }, 'baskiyeri-urun-sablonu.xlsx');
    }

    public function import(Request $request)
    {
        $vendor = $this->getVendor($request);
        $request->validate([
            'file' => ['required', 'file', 'mimes:xlsx,xls,csv', 'max:5120'],
        ]);

        try {
            Excel::import(new ProductsImport($vendor->id), $request->file('file'));
        } catch (\Throwable $e) {
            return back()->with('error', 'İçe aktarma hatası: ' . $e->getMessage());
        }

        return redirect()->route('vendor.products.index')->with('success', 'Ürünler Excel ile yüklendi.');
    }
}
