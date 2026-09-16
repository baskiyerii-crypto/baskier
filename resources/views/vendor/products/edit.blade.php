@extends('layouts.vendor')

@section('title', 'Ürün Düzenle')

@section('content')
<div class="card p-4" style="max-width:640px;">
        <form method="POST" action="{{ route('vendor.products.update', $product) }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <input type="hidden" name="product_type" value="physical">
            <div class="mb-3">
                <label class="form-label small fw-semibold">Ürün adı</label>
                <input type="text" name="name" class="form-control rounded-3" value="{{ old('name', $product->name) }}" required>
                <div class="mt-2">
                    <label class="form-label small fw-semibold">Ürün adı (EN)</label>
                    <input type="text" name="name_en" class="form-control rounded-3" value="{{ old('name_en', $product->name_en) }}">
                </div>
                @error('name')<div class="text-danger small">{{ $message }}</div>@enderror
            </div>
            <div class="mb-3">
                <label class="form-label small fw-semibold">Kategori</label>
                <select name="category_id" class="form-select rounded-3" required>
                    @foreach($categories as $c)<option value="{{ $c->id }}" @selected(old('category_id', $product->category_id) == $c->id)>{{ $c->name }}</option>@endforeach
                </select>
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label small fw-semibold">Fiyat (₺)</label>
                    <input type="number" name="price" step="0.01" min="0" class="form-control rounded-3" value="{{ old('price', $product->price) }}" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label small fw-semibold">Stok</label>
                    <input type="number" name="stock" min="0" class="form-control rounded-3" value="{{ old('stock', $product->stock) }}" required>
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label fw-semibold">Ürün görseli</label>
                @if($product->main_image)<div class="mb-2"><img src="{{ asset('storage/'.$product->main_image) }}" alt="" class="rounded" style="max-height:80px;"></div>@endif
                <input type="file" name="main_image" class="form-control" accept="image/jpeg,image/png,image/webp">
                <div class="form-text">JPG, PNG veya WebP · en fazla 2 MB · önerilen oran 4:3</div>
            </div>
            <div class="mb-3">
                <label class="form-label fw-semibold">Kısa açıklama</label>
                <textarea name="short_description" class="form-control" rows="2">{{ old('short_description', $product->short_description) }}</textarea>
                <label class="form-label fw-semibold mt-2">Kısa açıklama (EN)</label>
                <textarea name="short_description_en" class="form-control" rows="2">{{ old('short_description_en', $product->short_description_en) }}</textarea>
            </div>
            <div class="mb-3">
                <label class="form-label fw-semibold">Detaylı açıklama</label>
                <textarea name="description" class="form-control" rows="4">{{ old('description', $product->description) }}</textarea>
                <label class="form-label fw-semibold mt-2">Detaylı açıklama (EN)</label>
                <textarea name="description_en" class="form-control" rows="4">{{ old('description_en', $product->description_en) }}</textarea>
            </div>
            <div class="mb-3">
                <label class="form-label fw-semibold">Varyantlar (isteğe bağlı)</label>
                <textarea name="variant_lines" class="form-control font-monospace" rows="4" placeholder="Kırmızı XL|25|40|SKU-RED-XL&#10;Mavi L|0|20|SKU-BLUE-L">{{ old('variant_lines', $product->variants->map(fn($v) => $v->name.'|'.$v->price_adjustment.'|'.$v->stock.'|'.($v->sku ?? ''))->implode("\n")) }}</textarea>
                <div class="form-text">Her satır: Varyant Adı | Fiyat Farkı | Stok | SKU</div>
            </div>
            <div class="mb-4 form-check">
                <input type="checkbox" name="is_active" value="1" class="form-check-input" @checked(old('is_active', $product->is_active))>
                <label class="form-check-label">Aktif (sitede görünsün)</label>
            </div>
            <button type="submit" class="btn btn-success">Güncelle</button>
            <a href="{{ route('vendor.products.index') }}" class="btn btn-outline-secondary">İptal</a>
        </form>
</div>
@endsection
