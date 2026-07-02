<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category;
use App\Models\Vendor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AdminProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::with(['vendor', 'category']);
        if ($request->filled('category')) {
            $query->where('category_id', $request->category);
        }
        if ($request->filled('vendor')) {
            $query->where('vendor_id', $request->vendor);
        }
        if ($request->filled('q')) {
            $query->where('name', 'like', '%' . $request->q . '%');
        }
        $products = $query->latest()->paginate(20)->withQueryString();
        $categories = Category::orderBy('name')->get();
        $vendors = Vendor::orderBy('name')->get();
        return view('admin.products.index', compact('products', 'categories', 'vendors'));
    }

    public function create()
    {
        $categories = Category::orderBy('name')->get();
        $vendors = Vendor::orderBy('name')->get();
        return view('admin.products.create', compact('categories', 'vendors'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'category_id' => ['required', 'exists:categories,id'],
            'vendor_id' => ['required', 'exists:vendors,id'],
            'sku' => ['nullable', 'string', 'max:100'],
            'price' => ['required', 'numeric', 'min:0'],
            'stock' => ['required', 'integer', 'min:0'],
            'short_description' => ['nullable', 'string'],
            'description' => ['nullable', 'string'],
            'main_image' => ['nullable', 'image', 'max:2048'],
            'product_type' => ['nullable', 'in:physical,digital'],
            'digital_link' => ['nullable', 'url', 'max:500'],
            'is_active' => ['boolean'],
            'is_featured' => ['boolean'],
        ]);
        $validated['slug'] = \Illuminate\Support\Str::slug($validated['name']) . '-' . time();
        $validated['is_active'] = $request->boolean('is_active');
        $validated['is_featured'] = $request->boolean('is_featured');
        $validated['product_type'] = $validated['product_type'] ?? 'physical';
        if ($request->hasFile('main_image')) {
            $validated['main_image'] = $request->file('main_image')->store('products', 'public');
        }
        Product::create($validated);
        return redirect()->route('admin.products.index')->with('success', 'Ürün eklendi.');
    }

    public function edit(Product $product)
    {
        $categories = Category::orderBy('name')->get();
        $vendors = Vendor::orderBy('name')->get();
        return view('admin.products.edit', compact('product', 'categories', 'vendors'));
    }

    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'category_id' => ['required', 'exists:categories,id'],
            'vendor_id' => ['required', 'exists:vendors,id'],
            'sku' => ['nullable', 'string', 'max:100'],
            'price' => ['required', 'numeric', 'min:0'],
            'stock' => ['required', 'integer', 'min:0'],
            'short_description' => ['nullable', 'string'],
            'description' => ['nullable', 'string'],
            'main_image' => ['nullable', 'image', 'max:2048'],
            'is_active' => ['boolean'],
            'is_featured' => ['boolean'],
        ]);
        $validated['slug'] = \Illuminate\Support\Str::slug($validated['name']) . '-' . $product->id;
        $validated['is_active'] = $request->boolean('is_active');
        $validated['is_featured'] = $request->boolean('is_featured');
        if ($request->hasFile('main_image')) {
            if ($product->main_image) {
                Storage::disk('public')->delete($product->main_image);
            }
            $validated['main_image'] = $request->file('main_image')->store('products', 'public');
        }
        $product->update($validated);
        return redirect()->route('admin.products.index')->with('success', 'Ürün güncellendi.');
    }

    public function destroy(Product $product)
    {
        $product->delete();
        return redirect()->route('admin.products.index')->with('success', 'Ürün silindi.');
    }
}
