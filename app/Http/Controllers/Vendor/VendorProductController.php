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

    private function ensurePhysicalProductsTrack($vendor): void
    {
        $tracks = $vendor->registration_tracks ?? [];
        if ($tracks === [] || $vendor->hasTrack('physical_products')) {
            return;
        }
        if ($vendor->prefersOutdoorPanel()) {
            abort(403, 'Hazır ürün satışı kolu gerekli.');
        }

        $tracks[] = 'physical_products';
        $vendor->forceFill([
            'registration_tracks' => array_values(array_unique($tracks)),
        ])->save();
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
        $this->ensurePhysicalProductsTrack($vendor);
        if (! $vendor->hasApprovedTaxPlate()) {
            return redirect()->route('vendor.documents.index')
                ->with('error', 'Fiziksel ürün eklemek için onaylı vergi levhası yüklemelisiniz.');
        }
        $categories = Category::where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('channel')->orWhere('channel', 'physical_quote');
            })
            ->orderBy('name')->get();

        return view('vendor.products.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $vendor = $this->getVendor($request);
        $this->ensurePhysicalProductsTrack($vendor);
        if (! $vendor->hasApprovedTaxPlate()) {
            return redirect()->route('vendor.documents.index')
                ->with('error', 'Fiziksel ürün eklemek için onaylı vergi levhası yüklemelisiniz.');
        }
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'name_en' => ['nullable', 'string', 'max:255'],
            'category_id' => ['required', 'exists:categories,id'],
            'price' => ['required', 'numeric', 'min:0'],
            'stock' => ['required', 'integer', 'min:0'],
            'short_description' => ['nullable', 'string'],
            'short_description_en' => ['nullable', 'string'],
            'main_image' => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp', 'max:2048'],
            'gallery' => ['nullable', 'array', 'max:8'],
            'gallery.*' => ['image', 'mimes:jpeg,jpg,png,webp', 'max:2048'],
            'description' => ['nullable', 'string'],
            'description_en' => ['nullable', 'string'],
            'variant_lines' => ['nullable', 'string'],
        ]);
        $validated['vendor_id'] = $vendor->id;
        unset($validated['gallery']);
        $validated['slug'] = \Illuminate\Support\Str::slug($validated['name']) . '-' . time();
        $validated['is_active'] = false;
        $validated['product_type'] = 'physical';
        $validated['digital_link'] = null;
        if (\Illuminate\Support\Facades\Schema::hasColumn('products', 'moderation_status')) {
            $validated['moderation_status'] = Product::MODERATION_PENDING;
            $validated['submitted_for_moderation_at'] = now();
            $validated['moderation_note'] = null;
        }
        if ($request->hasFile('main_image')) {
            $validated['main_image'] = $request->file('main_image')->store('products', 'public');
        }
        $product = Product::create($validated);
        $this->syncVariants($product, (string) $request->input('variant_lines', ''));
        if ($request->hasFile('gallery')) {
            $product->attachGallery($request->file('gallery', []));
        }
        return redirect()->route('vendor.products.index')->with('success', 'Ürün eklendi; admin onayından sonra yayınlanır.');
    }

    public function edit(Request $request, Product $product)
    {
        $vendor = $this->getVendor($request);
        if ($product->vendor_id !== $vendor->id) {
            abort(403, 'Bu ürün size ait değil.');
        }
        $product->load(['variants', 'images']);
        $categories = Category::where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('channel')->orWhere('channel', 'physical_quote');
            })
            ->orderBy('name')->get();

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
            'name_en' => ['nullable', 'string', 'max:255'],
            'category_id' => ['required', 'exists:categories,id'],
            'price' => ['required', 'numeric', 'min:0'],
            'stock' => ['required', 'integer', 'min:0'],
            'short_description' => ['nullable', 'string'],
            'short_description_en' => ['nullable', 'string'],
            'is_active' => ['boolean'],
            'main_image' => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp', 'max:2048'],
            'gallery' => ['nullable', 'array', 'max:8'],
            'gallery.*' => ['image', 'mimes:jpeg,jpg,png,webp', 'max:2048'],
            'description' => ['nullable', 'string'],
            'description_en' => ['nullable', 'string'],
            'variant_lines' => ['nullable', 'string'],
        ]);
        $validated['slug'] = \Illuminate\Support\Str::slug($validated['name']) . '-' . $product->id;
        unset($validated['gallery']);
        $validated['is_active'] = false;
        $validated['product_type'] = 'physical';
        $validated['digital_link'] = null;
        if (\Illuminate\Support\Facades\Schema::hasColumn('products', 'moderation_status')) {
            $validated['moderation_status'] = Product::MODERATION_PENDING;
            $validated['submitted_for_moderation_at'] = now();
            $validated['moderation_note'] = null;
        }
        if ($request->hasFile('main_image')) {
            if ($product->main_image) {
                Storage::disk('public')->delete($product->main_image);
            }
            $validated['main_image'] = $request->file('main_image')->store('products', 'public');
        }
        $product->update($validated);
        $this->syncVariants($product, (string) $request->input('variant_lines', ''));
        if ($request->hasFile('gallery')) {
            $product->attachGallery($request->file('gallery', []));
        }
        return redirect()->route('vendor.products.index')->with('success', 'Ürün güncellendi; yeniden onay bekliyor.');
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
