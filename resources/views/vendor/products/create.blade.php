@extends('layouts.vendor')

@section('title', 'Yeni Ürün')

@section('content')
<div class="card p-4" style="max-width:640px;">
        <p class="small text-muted mb-3">Yalnızca fiziksel ürün ekleyebilirsiniz. Teklif ve freelancer hizmetleri ilgili modüller üzerinden alınır.</p>
        <form method="POST" action="{{ route('vendor.products.store') }}" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="product_type" value="physical">
            <div class="mb-3">
                <label class="form-label small fw-semibold">Ürün adı</label>
                <input type="text" name="name" class="form-control rounded-3" value="{{ old('name') }}" required>
                @error('name')<div class="text-danger small">{{ $message }}</div>@enderror
            </div>
            <div class="mb-3">
                <label class="form-label small fw-semibold">Ürün adı (EN)</label>
                <input type="text" name="name_en" class="form-control rounded-3" value="{{ old('name_en') }}">
            </div>
            <div class="mb-3">
                <label class="form-label small fw-semibold">Kategori</label>
                <select name="category_id" class="form-select rounded-3" required>
                    @foreach($categories as $c)<option value="{{ $c->id }}" @selected(old('category_id') == $c->id)>{{ $c->name }}</option>@endforeach
                </select>
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label small fw-semibold">Fiyat (₺)</label>
                    <input type="number" name="price" step="0.01" min="0" inputmode="decimal" class="form-control rounded-3 number-only-input" value="{{ old('price') }}" required>
                    @error('price')<div class="text-danger small">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label small fw-semibold">Stok</label>
                    <input type="number" name="stock" min="0" inputmode="numeric" class="form-control rounded-3 number-only-input" value="{{ old('stock', 0) }}" required>
                    @error('stock')<div class="text-danger small">{{ $message }}</div>@enderror
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label fw-semibold">Ürün görseli</label>
                <input type="file" name="main_image" class="form-control" accept="image/jpeg,image/png,image/webp">
                <div class="form-text">JPG, PNG veya WebP · en fazla 2 MB · önerilen oran 4:3</div>
                @error('main_image')<div class="text-danger small">{{ $message }}</div>@enderror
            </div>
            <div class="mb-3">
                <label class="form-label fw-semibold">Galeri</label>
                <input type="file" name="gallery[]" class="form-control" accept="image/jpeg,image/png,image/webp" multiple>
            </div>
            <div class="mb-3">
                <label class="form-label fw-semibold">Kısa açıklama</label>
                <textarea name="short_description" class="form-control" rows="2">{{ old('short_description') }}</textarea>
            </div>
            <div class="mb-3">
                <label class="form-label fw-semibold">Kısa açıklama (EN)</label>
                <textarea name="short_description_en" class="form-control" rows="2">{{ old('short_description_en') }}</textarea>
            </div>
            <div class="mb-3">
                <label class="form-label fw-semibold">Detaylı açıklama</label>
                <textarea name="description" class="form-control" rows="4">{{ old('description') }}</textarea>
            </div>
            <div class="mb-3">
                <label class="form-label fw-semibold">Detaylı açıklama (EN)</label>
                <textarea name="description_en" class="form-control" rows="4">{{ old('description_en') }}</textarea>
            </div>
            <div class="mb-3">
                <label class="form-label fw-semibold">Varyantlar (isteğe bağlı)</label>
                <textarea name="variant_lines" class="form-control font-monospace" rows="4" placeholder="Kırmızı XL|25|40|SKU-RED-XL&#10;Mavi L|0|20|SKU-BLUE-L">{{ old('variant_lines') }}</textarea>
                <div class="form-text">Her satır: Varyant Adı | Fiyat Farkı | Stok | SKU</div>
            </div>
            <button type="submit" class="btn btn-success">Ürünü Ekle</button>
            <a href="{{ route('vendor.products.index') }}" class="btn btn-outline-secondary">İptal</a>
        </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.number-only-input').forEach(function(input) {
        input.addEventListener('keydown', function(e) {
            const allowed = ['Backspace', 'Delete', 'Tab', 'ArrowLeft', 'ArrowRight', 'ArrowUp', 'ArrowDown', '.', ',', '-', 'Enter'];
            if (allowed.includes(e.key)) return;
            if (e.ctrlKey || e.metaKey) return;
            if (!/^[0-9]$/.test(e.key)) {
                e.preventDefault();
            }
        });
    });
});
</script>
@endsection
