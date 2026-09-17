@extends('layouts.vendor')

@section('title', 'Ürün Düzenle - Satıcı Paneli')

@section('content')
<div class="mb-5">
    <a href="{{ route('vendor.products.index') }}" class="inline-flex items-center text-xs font-semibold text-muted hover:text-ink transition-colors">
        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
        Ürünlerime Dön
    </a>
</div>

<div class="max-w-2xl">
    <h1 class="font-heading text-2xl font-bold tracking-tight text-ink mb-1">Ürün Düzenle</h1>
    <p class="text-xs text-muted mb-6">Mevcut ürünün fiyat, stok, görsel ve varyant bilgilerini güncelleyin.</p>

    <div class="by-card p-6 md:p-8 bg-surface border border-border">
        <form method="POST" action="{{ route('vendor.products.update', $product) }}" enctype="multipart/form-data" class="space-y-4">
            @csrf
            @method('PUT')
            <input type="hidden" name="product_type" value="physical">
            
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-muted mb-1">Ürün Adı (TR) <span class="text-red-500">*</span></label>
                    <input type="text" name="name" class="form-control text-xs" value="{{ old('name', $product->name) }}" required>
                    @error('name')<div class="text-red-600 text-[11px] mt-1">{{ $message }}</div>@enderror
                </div>
                <div>
                    <label class="block text-xs font-semibold text-muted mb-1">Ürün Adı (EN)</label>
                    <input type="text" name="name_en" class="form-control text-xs" value="{{ old('name_en', $product->name_en) }}">
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-muted mb-1">Kategori <span class="text-red-500">*</span></label>
                <select name="category_id" class="form-control text-xs" required>
                    @foreach($categories as $c)<option value="{{ $c->id }}" @selected(old('category_id', $product->category_id) == $c->id)>{{ $c->name }}</option>@endforeach
                </select>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-muted mb-1">Fiyat (₺) <span class="text-red-500">*</span></label>
                    <input type="number" name="price" step="0.01" min="0" class="form-control text-xs" value="{{ old('price', $product->price) }}" required>
                    @error('price')<div class="text-red-600 text-[11px] mt-1">{{ $message }}</div>@enderror
                </div>
                <div>
                    <label class="block text-xs font-semibold text-muted mb-1">Stok Adedi <span class="text-red-500">*</span></label>
                    <input type="number" name="stock" min="0" class="form-control text-xs" value="{{ old('stock', $product->stock) }}" required>
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-muted mb-1">Ürün Görseli</label>
                @if($product->main_image)
                    <div class="mb-2">
                        <img src="{{ asset('storage/'.$product->main_image) }}" alt="" class="rounded-lg border border-border h-20 w-20 object-cover">
                    </div>
                @endif
                <input type="file" name="main_image" class="form-control text-xs" accept="image/jpeg,image/png,image/webp">
                <div class="text-[11px] text-muted mt-1">JPG, PNG veya WebP · en fazla 2 MB · önerilen oran 4:3</div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-muted mb-1">Kısa Açıklama (TR)</label>
                    <textarea name="short_description" class="form-control text-xs" rows="2">{{ old('short_description', $product->short_description) }}</textarea>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-muted mb-1">Kısa Açıklama (EN)</label>
                    <textarea name="short_description_en" class="form-control text-xs" rows="2">{{ old('short_description_en', $product->short_description_en) }}</textarea>
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-muted mb-1">Detaylı Açıklama (TR)</label>
                <textarea name="description" class="form-control text-xs" rows="4">{{ old('description', $product->description) }}</textarea>
            </div>
            <div>
                <label class="block text-xs font-semibold text-muted mb-1">Detaylı Açıklama (EN)</label>
                <textarea name="description_en" class="form-control text-xs" rows="4">{{ old('description_en', $product->description_en) }}</textarea>
            </div>

            <div class="p-4 rounded-xl border border-border bg-canvas/40 space-y-1.5">
                <label class="block text-xs font-bold text-ink">Varyantlar (İsteğe bağlı)</label>
                <textarea name="variant_lines" class="form-control text-xs font-mono" rows="3" placeholder="Kırmızı XL|25|40|SKU-RED-XL&#10;Mavi L|0|20|SKU-BLUE-L">{{ old('variant_lines', $product->variants->map(fn($v) => $v->name.'|'.$v->price_adjustment.'|'.$v->stock.'|'.($v->sku ?? ''))->implode("\n")) }}</textarea>
                <div class="text-[11px] text-muted">Format: Varyant Adı | Fiyat Farkı | Stok | SKU (Her satıra bir varyant)</div>
            </div>

            <div class="flex items-center gap-2 pt-2">
                <input type="checkbox" name="is_active" id="is_active" value="1" class="h-4 w-4 rounded border-border text-cta focus:ring-cta" @checked(old('is_active', $product->is_active))>
                <label for="is_active" class="text-xs font-medium text-ink cursor-pointer">Aktif (Ürün sitede yayında ve satılabilir olsun)</label>
            </div>

            <div class="pt-3 border-t border-border flex items-center justify-between">
                <button type="submit" class="btn btn-cta text-xs py-2 px-6">Değişiklikleri Güncelle</button>
                <a href="{{ route('vendor.products.index') }}" class="btn btn-secondary text-xs">İptal</a>
            </div>
        </form>
    </div>
</div>
@endsection
