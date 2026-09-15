<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Imports\ProductsImport;
use App\Models\Product;
use App\Models\Category;
use App\Models\ProductVariant;
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
        $query = $vendor->products()->with('category')->latest();

        if ($request->filled('q')) {
            $search = $request->input('q');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('short_description', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->input('status') === 'active');
        }

        $products = $query->paginate(20)->withQueryString();

        return view('vendor.products.index', compact('vendor', 'products'));
    }

    public function create(Request $request)
    {
        $vendor = $this->getVendor($request);
        $categories = Category::where('is_active', true)->orderBy('name')->get();
        $freelancerModuleActive = $vendor->hasActiveFreelancerModule();

        return view('vendor.products.create', compact('categories', 'freelancerModuleActive'));
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
            'description' => ['nullable', 'string'],
            'variant_lines' => ['nullable', 'string'],
        ]);
        $validated['vendor_id'] = $vendor->id;
        $validated['slug'] = \Illuminate\Support\Str::slug($validated['name']) . '-' . time();
        $validated['is_active'] = true;
        $validated['product_type'] = $validated['product_type'] ?? 'physical';
        if ($validated['product_type'] === 'digital' && ! $vendor->hasActiveFreelancerModule()) {
            return back()
                ->withInput()
                ->with('error', 'Dijital/freelancer ürün için önce modül aboneliğini aktif etmelisiniz.');
        }
        if ($request->hasFile('main_image')) {
            $validated['main_image'] = $request->file('main_image')->store('products', 'public');
        }
        if (($validated['product_type'] ?? '') === 'digital') {
            $validated['digital_link'] = $request->digital_link;
        }
        $product = Product::create($validated);
        $this->syncVariants($product, (string) $request->input('variant_lines', ''));
        return redirect()->route('vendor.products.index')->with('success', 'Ürün eklendi.');
    }

    public function edit(Request $request, Product $product)
    {
        $vendor = $this->getVendor($request);
        if ($product->vendor_id !== $vendor->id) {
            abort(403, 'Bu ürün size ait değil.');
        }
        $product->load('variants');
        $categories = Category::where('is_active', true)->orderBy('name')->get();
        $freelancerModuleActive = $vendor->hasActiveFreelancerModule();

        return view('vendor.products.edit', compact('product', 'categories', 'freelancerModuleActive'));
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
            'description' => ['nullable', 'string'],
            'variant_lines' => ['nullable', 'string'],
        ]);
        $validated['slug'] = \Illuminate\Support\Str::slug($validated['name']) . '-' . $product->id;
        $validated['is_active'] = $request->boolean('is_active');
        $validated['product_type'] = $validated['product_type'] ?? 'physical';
        if ($validated['product_type'] === 'digital' && ! $vendor->hasActiveFreelancerModule()) {
            return back()
                ->withInput()
                ->with('error', 'Dijital/freelancer ürün için önce modül aboneliğini aktif etmelisiniz.');
        }
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
        $this->syncVariants($product, (string) $request->input('variant_lines', ''));
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
            Excel::import(new ProductsImport($vendor->id, $vendor->hasActiveFreelancerModule()), $request->file('file'));
        } catch (\Throwable $e) {
            return back()->with('error', 'İçe aktarma hatası: ' . $e->getMessage());
        }

        return redirect()->route('vendor.products.index')->with('success', 'Ürünler Excel ile yüklendi.');
    }

    private function syncVariants(Product $product, string $rawLines): void
    {
        $lines = preg_split('/\r\n|\r|\n/', $rawLines) ?: [];
        $payload = [];
        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '') {
                continue;
            }
            $parts = array_map('trim', explode('|', $line));
            $name = $parts[0] ?? '';
            if ($name === '') {
                continue;
            }
            $payload[] = [
                'name' => $name,
                'price_adjustment' => (float) ($parts[1] ?? 0),
                'stock' => max(0, (int) ($parts[2] ?? 0)),
                'sku' => ($parts[3] ?? null) ?: null,
                'attributes' => null,
            ];
        }

        ProductVariant::query()->where('product_id', $product->id)->delete();
        if ($payload === []) {
            return;
        }
        foreach ($payload as $row) {
            $row['product_id'] = $product->id;
            ProductVariant::query()->create($row);
        }
    }
}
